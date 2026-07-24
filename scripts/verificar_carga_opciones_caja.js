/*
 * Prueba de regresion para la carga asincronica de Caja y Punto de expedicion.
 *
 * Uso:
 *   cscript //E:JScript //nologo scripts\verificar_carga_opciones_caja.js
 *
 * Ejecuta las funciones reales de inicio.js sobre un DOM simulado y no accede
 * a la base de datos ni a servicios HTTP.
 */

function afirmar(condicion, mensaje) {
    if (!condicion) {
        throw new Error(mensaje);
    }
    WScript.Echo("[OK] " + mensaje);
}

function extraerFuncion(fuente, nombre) {
    var inicio = fuente.indexOf("function " + nombre + "(");
    if (inicio < 0) {
        throw new Error("No se encontro la funcion " + nombre + ".");
    }

    var inicioBloque = fuente.indexOf("{", inicio);
    var nivel = 0;
    var indice;
    for (indice = inicioBloque; indice < fuente.length; indice++) {
        if (fuente.charAt(indice) === "{") {
            nivel++;
        } else if (fuente.charAt(indice) === "}") {
            nivel--;
            if (nivel === 0) {
                return fuente.substring(inicio, indice + 1);
            }
        }
    }

    throw new Error("La funcion " + nombre + " esta incompleta.");
}

function leerArchivo(ruta) {
    var sistemaArchivos = new ActiveXObject("Scripting.FileSystemObject");
    var archivo = sistemaArchivos.OpenTextFile(ruta, 1, false);
    var contenido = archivo.ReadAll();
    archivo.Close();
    return contenido;
}

try {
    var sistemaArchivos = new ActiveXObject("Scripting.FileSystemObject");
    var carpetaScripts = sistemaArchivos.GetParentFolderName(WScript.ScriptFullName);
    var base = sistemaArchivos.GetParentFolderName(carpetaScripts);
    var fuenteInicio = leerArchivo(sistemaArchivos.BuildPath(base, "js_system\\inicio.js"));
    var fuenteHtml = leerArchivo(sistemaArchivos.BuildPath(base, "system\\inicio.html"));

    eval(extraerFuncion(fuenteInicio, "obtenerDestinosOptionCaja"));
    eval(extraerFuncion(fuenteInicio, "limpiarDestinosOptionCaja"));
    eval(extraerFuncion(fuenteInicio, "buscarOptionCaja"));
    eval(extraerFuncion(fuenteInicio, "seleccionarcaja"));
    eval(extraerFuncion(fuenteInicio, "buscarOptionCaja2"));

    var elementos = {};
    var document = {
        getElementById: function (id) {
            return elementos.hasOwnProperty(id) ? elementos[id] : null;
        }
    };
    var console = { log: function () {} };
    var ajaxActual = null;
    var llamadasAjax = 0;
    var controlesCaja = 0;
    var userid = "1";
    var passuser = "prueba";
    var navegador = "prueba";
    var cod_localFKUSer = "1";
    var cajapredeterminada = "5";

    function obtener_datos_user() {}
    function respuestaJqueryAjax(valor) {
        return valor === "exito";
    }
    function controldecaja() {
        controlesCaja++;
    }
    function cargarConectividad() {}
    function manejadordeerroresjquery() {}
    function ver_vetana_informativa(mensaje) {
        throw new Error(mensaje);
    }
    function GuardarArchivosLog(mensaje) {
        throw new Error(mensaje);
    }

    var $ = {
        ajax: function (configuracion) {
            llamadasAjax++;
            ajaxActual = configuracion;
        },
        parseJSON: function (texto) {
            return eval("(" + texto + ")");
        }
    };

    var respuesta = "{\"1\":\"exito\",\"2\":\"<option value='5'>Caja 01</option>\",\"4\":\"<option value='5'>001-001</option>\"}";

    buscarOptionCaja();
    afirmar(llamadasAjax === 0, "Sin destinos no se inicia una solicitud innecesaria.");

    elementos.inptSeleccPuntoExpedicionVenta = { innerHTML: "", value: "anterior" };
    buscarOptionCaja();
    afirmar(llamadasAjax === 1, "Con un destino disponible se solicita la informacion.");
    delete elementos.inptSeleccPuntoExpedicionVenta;
    ajaxActual.success(respuesta);
    afirmar(controlesCaja === 0, "Si el destino desaparece, la respuesta se ignora sin controlar Caja.");

    elementos.inptSeleccPuntoExpedicionVenta = { innerHTML: "", value: "anterior" };
    buscarOptionCaja();
    ajaxActual.success(respuesta);
    afirmar(
        elementos.inptSeleccPuntoExpedicionVenta.innerHTML.indexOf("001-001") >= 0
            && elementos.inptSeleccPuntoExpedicionVenta.value === "",
        "Punto de expedicion se completa aunque Caja no este renderizada."
    );

    delete elementos.inptSeleccPuntoExpedicionVenta;
    elementos.inptcajaAperturaCierreCaja = {
        innerHTML: "",
        value: "",
        options: [{ value: "5", text: "Caja 01" }],
        selectedIndex: 0
    };
    elementos.pCaja = { innerHTML: "" };
    buscarOptionCaja();
    ajaxActual.success(respuesta);
    afirmar(
        elementos.inptcajaAperturaCierreCaja.innerHTML.indexOf("Caja 01") >= 0
            && elementos.inptcajaAperturaCierreCaja.value === "5"
            && elementos.pCaja.innerHTML === "Caja 01",
        "Caja se completa y selecciona aunque Punto de expedicion no este renderizado."
    );
    afirmar(controlesCaja === 1, "El control de Caja se ejecuta solamente cuando su selector existe.");

    elementos = {};
    ajaxActual = null;
    buscarOptionCaja2("1");
    afirmar(ajaxActual === null, "La recarga por local se omite si sus controles no existen.");

    elementos.inptlocalAperturaCierre = { value: "1" };
    elementos.inptcajaAperturaCierreCaja = { innerHTML: "" };
    buscarOptionCaja2("1");
    delete elementos.inptcajaAperturaCierreCaja;
    ajaxActual.success(respuesta);
    afirmar(controlesCaja === 1, "La recarga por local tolera que Caja desaparezca durante la solicitud.");

    afirmar(
        fuenteInicio.indexOf("if(selectCajaSesion)") >= 0,
        "El inicio del perfil restringido verifica que el selector de Caja exista."
    );
    afirmar(
        fuenteHtml.indexOf("inicio.js?x=carga-opciones-caja-segura-20260724-1") >= 0,
        "La pantalla solicita la version corregida de inicio.js."
    );

    WScript.Echo("RESULTADO: APROBADO");
} catch (error) {
    WScript.StdErr.WriteLine("[ERROR] " + error.message);
    WScript.Quit(1);
}

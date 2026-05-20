var controldebusquedadInformeInformacionProtocolo = true;
var totalregistroinformeInformacionProtocolo = 0;
var registrocargadoInformacionProtocolo = 0;
var codInformacionProtocolo = "";

function minimizarabmInformacionProtocolo() {
    document.getElementById("divAbmInformacionprotocolo").style.display = "none";
    document.getElementById("divMinimizadoInformacionProtocolo").style.display = "";
}

function limpiarcamposFiltrosInformacionProtocolo() {
    document.getElementById('inptBuscarInformacionProtocolo1').value='';
    document.getElementById('inptBuscarInformacionProtocolo2').value='';
    document.getElementById('inptBuscarInformacionProtocolo3').value='';
    document.getElementById('inptBuscarInformacionProtocolo5').value='';
    document.getElementById('inptBuscarInformacionProtocolo7').value='';
    document.getElementById('inptSeleccFiltroEstadoInformacionProtocolo').checked=true;
    buscarVistaInformacionProtocolos();
}

function limpiarcamposInformacionProtocolo() {
    codInformacionProtocolo = "";
    document.getElementById("inptCodAbmInformacionProtocolo").value = "";
    document.getElementById("inptNombreAbmInformacionProtocolo").value = "";
    document.getElementById("inptDescripcionAbmInformacionProtocolo").value = "";
    document.getElementById("inptEstadoAbmInformacionProtocolo").value = "activo";
}

function verCerrarVentanaAbmInformacionProtocolo(mostrar, abm) {
    if (mostrar) {
        document.getElementById("divMinimizadoInformacionProtocolo").style.display = "none";
        
        if (abm) {
            document.getElementById('divAbmInformacionprotocolo2').style.display = "";
            document.getElementById('divAbmInformacionprotocolo1').style.display = "none";
        } else {
            document.getElementById("divAbmInformacionprotocolo").style.display = "";
            document.getElementById('divAbmInformacionprotocolo1').style.display = "";
            document.getElementById('divAbmInformacionprotocolo2').style.display = "none";
        }
    } else {
        if (abm) {
            document.getElementById('divAbmInformacionprotocolo2').style.display = "none";
            document.getElementById('divAbmInformacionprotocolo1').style.display = "";
        } else {
            document.getElementById("divAbmInformacionprotocolo").style.display = "none";
            document.getElementById('divAbmInformacionprotocolo1').style.display = "none";
            document.getElementById('divAbmInformacionprotocolo2').style.display = "";
        }

        if (ventanaAnterior.length > 0) {
            const ultimoElemento = ventanaAnterior[ventanaAnterior.length - 1];
            document.getElementById(ultimoElemento).style.display = "";
            ventanaAnterior.pop();
        }
    }
}

function buscarVistaInformacionProtocolos() {
    const id = document.getElementById("inptBuscarInformacionProtocolo1").value;
    const nombre = document.getElementById("inptBuscarInformacionProtocolo2").value;
    const estado = document.getElementById("inptBuscarInformacionProtocolo5").value;
    const ocultarInactivo = document.getElementById("inptSeleccFiltroEstadoInformacionProtocolo").checked;
    const usuario_creador= document.getElementById('inptBuscarInformacionProtocolo7').value;

    buscarVistaInformacionProtocolos2(id, nombre, estado, usuario_creador, 1, ocultarInactivo);
}

var mensajes_protocolos_empresariales = [];
function buscarVistaInformacionProtocolos2(id, nombre, estado, usuario_creador, limite, ocultarInactivo) {
    let datos = new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "buscarVista");
    datos.append("id", id);
    datos.append("nombre", nombre);
    datos.append("estado", estado);
    datos.append("usuario_creador", usuario_creador);
    if (ocultarInactivo) {
        datos.append("ocultar_inactivo", ocultarInactivo);
    }

    if (limite != 0) {
        controldebusquedadInformeInformacionProtocolo = true;
        registrocargadoInformacionProtocolo = 0;
        const tabla = document.getElementById("table_frm_VistaInformacionProtocolo");
        if (tabla) {
            tabla.innerHTML = typeof paginacargando !== "undefined" ? paginacargando : "";
        }
        datos.append("limite", limite);
    }
    
	verCerrarEfectoCargando("1")
    $.ajax({
        data: datos,
        url: "../php_system/abmInformacionProtocolo.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        error: function (jqXHR, textstatus) {
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
            ver_vetana_informativa("SE HA PRODUCTIDO UN ERROR");
            controldebusquedadInformeInformacionProtocolo = false;
	        verCerrarEfectoCargando("")
        },
        success: function (responseText) {
            Respuesta = responseText;
            console.log(Respuesta);
	        verCerrarEfectoCargando("")
            try {
                var datos = $.parseJSON(Respuesta);
                Respuesta = datos["1"];
                Respuesta = respuestaJqueryAjax(Respuesta);
                if (Respuesta) {
                    const tabla = document.getElementById("table_frm_VistaInformacionProtocolo");
                    if (tabla) {
                        tabla.innerHTML = datos["2"];
                    }

                    if (limite != 0) {
                        registrocargadoInformacionProtocolo = Number(datos["4"]);
                        totalregistroinformeInformacionProtocolo = Number(datos["5"]);
                        document.getElementById("inptRegistoCargadoInformacionProtocolo").value = registrocargadoInformacionProtocolo;
                        document.getElementById("tbProcessInformeInformacionProtocolo").style.display = "none";
                        if (controldebusquedadInformeInformacionProtocolo && registrocargadoInformacionProtocolo < totalregistroinformeInformacionProtocolo) {
                            document.getElementById("tbProcessInformeInformacionProtocolo").style.display = "";
                            var porce=((registrocargadoInformacionProtocolo*100)/totalregistroinformeInformacionProtocolo).toFixed(0)
                            document.getElementById("divProgressInformeInformacionProtocolo").style.width=porce+"%";

                            buscarMasVistaInformacionProtocolos2(id, nombre, estado, usuario_creador, '1 OFFSET '+registrocargadoInformacionProtocolo, ocultarInactivo)
                        }
                    } else {
                        mensajes_protocolos_empresariales= [];
                        Array.from(datos["3"]).forEach(function (elemento) {
                            mensajes_protocolos_empresariales.push(elemento.descripcion);
                        });
                    }
                }
            } catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ");
                var titulo = "Error: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
            }
        }
    });
}

function buscarMasVistaInformacionProtocolos2(id, nombre, estado, usuario_creador, limite, ocultarInactivo) {
    let datos = new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "buscarVista");
    datos.append("id", id);
    datos.append("nombre", nombre);
    datos.append("estado", estado);
    datos.append("usuario_creador", usuario_creador);
    datos.append("limite", limite);
    if (ocultarInactivo) {
        datos.append("ocultar_inactivo", ocultarInactivo);
    }
    
    if (!controldebusquedadInformeInformacionProtocolo) {
        return;
    }

    $.ajax({
        data: datos,
        url: "../php_system/abmInformacionProtocolo.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        error: function (jqXHR, textstatus) {
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
            ver_vetana_informativa("SE HA PRODUCTIDO UN ERROR");
            controldebusquedadInformeInformacionProtocolo = false;
        },
        success: function (responseText) {
            if (!controldebusquedadInformeInformacionProtocolo) {
                return;
            }

            Respuesta = responseText;
            console.log(Respuesta);
            try {
                var datos = $.parseJSON(Respuesta);
                Respuesta = datos["1"];
                Respuesta = respuestaJqueryAjax(Respuesta);

                if (!controldebusquedadInformeInformacionProtocolo) {
                    return;
                }

                if (Respuesta) {
                    const tabla = document.getElementById("table_frm_VistaInformacionProtocolo");
                    if (tabla) {
                        tabla.innerHTML += datos["2"];
                    }

                    registrocargadoInformacionProtocolo += Number(datos["4"]);
                    document.getElementById("inptRegistoCargadoInformacionProtocolo").value = registrocargadoInformacionProtocolo;

                    if (controldebusquedadInformeInformacionProtocolo && registrocargadoInformacionProtocolo < totalregistroinformeInformacionProtocolo) {
                        buscarMasVistaInformacionProtocolos2(id, nombre, estado, usuario_creador, '10 OFFSET '+registrocargadoInformacionProtocolo, ocultarInactivo)
                    
                        document.getElementById("tbProcessInformeInformacionProtocolo").style.display = "";
                        var porce=((registrocargadoInformacionProtocolo*100)/totalregistroinformeInformacionProtocolo).toFixed(0)
                        document.getElementById("divProgressInformeInformacionProtocolo").style.width=porce+"%";
                    }
                }
            } catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ");
                var titulo = "Error: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
            }
        }
    });
}

function cancelarInformeInformacionProtocolo() {
    controldebusquedadInformeInformacionProtocolo = false;

    const progreso = document.getElementById("divProgressInformeInformacionProtocolo");
    if (progreso && registrocargadoInformacionProtocolo < totalregistroinformeInformacionProtocolo) {
        progreso.style.backgroundColor = "#ff5722";
    }
}

function obtenerDatosInformacionProtocolo(elemento) {
    cancelarInformeInformacionProtocolo();

    codInformacionProtocolo = $(elemento).children("#td_id").html();
    document.getElementById("inptCodAbmInformacionProtocolo").value = codInformacionProtocolo;
    document.getElementById("inptNombreAbmInformacionProtocolo").value = $(elemento).children("#td_datos_1").html();
    document.getElementById("inptDescripcionAbmInformacionProtocolo").value = $(elemento).children("#td_datos_2").html();
    document.getElementById("inptEstadoAbmInformacionProtocolo").value = ($(elemento).children("#td_datos_3").html() || "").toLowerCase();

    verCerrarVentanaAbmInformacionProtocolo(true, "divListadoInformacionProtocolo");
}

function verificarCamposInformacionProtocolo() {
    const nombre = document.getElementById("inptNombreAbmInformacionProtocolo").value;
    const descripcion = document.getElementById("inptDescripcionAbmInformacionProtocolo").value;
    const estado = document.getElementById("inptEstadoAbmInformacionProtocolo").value;

    if (!nombre) {
        ver_vetana_informativa("Faltan datos", "El campo nombre es obligatorio.", "advertencia");
        return false;
    }

    abmInformacionProtocolo(nombre, descripcion, estado);
}

function abmInformacionProtocolo(nombre, descripcion, estado) {
    let datos = new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "nuevo/editar");
    datos.append("id", codInformacionProtocolo || document.getElementById("inptCodAbmInformacionProtocolo").value);
    datos.append("nombre", nombre);
    datos.append("descripcion", descripcion);
    datos.append("estado", estado);

    verCerrarEfectoCargando("1");
    $.ajax({
        data: datos,
        url: "../php_system/abmInformacionProtocolo.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        error: function (jqXHR, textstatus) {
            verCerrarEfectoCargando("");
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
            ver_vetana_informativa("SE HA PRODUCTIDO UN ERROR");
        },
        success: function (responseText) {
            Respuesta = responseText;
            console.log(Respuesta);
            try {
                var datos = $.parseJSON(Respuesta);
                Respuesta = datos["1"];
                if (Respuesta == "exito") {
                    codInformacionProtocolo = datos["id"];
                    document.getElementById("inptCodAbmInformacionProtocolo").value = codInformacionProtocolo;
                    ver_vetana_informativa("Datos guardados.", "", "info");
                    verCerrarVentanaAbmInformacionProtocolo(false, "divListadoInformacionProtocolo");
                    buscarVistaInformacionProtocolos();
                } else {
                    respuestaJqueryAjax(Respuesta);
                }
            } catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ");
                var titulo = "Error: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
            } finally {
                verCerrarEfectoCargando("");
            }
        }
    });
}

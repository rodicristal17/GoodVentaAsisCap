var cod_mecanico_dental = "";
var filtro_fecha_mecanico_dental= 1;
var cod_usuario_telar_mecanico_dental = "";
var temporizador_busqueda_usuario_telar_mecanico = null;
var secuencia_busqueda_usuario_telar_mecanico = 0;

function verCerrarVentanaMecanicoDental(mostrar, mostrarAbm) {
    if(controlacceso("VERLISTADOMECANICODENTAL","accion")==false){ return;}
    document.getElementById('divMinimizadoMecanicoDental').style.display= 'none';
    if (mostrar) {
        $("div[id=divAbmMecanicoDental]").fadeIn(250);
        if (mostrarAbm) {
            buscarTiposTrabajo();
            cargarCuentasTelarMecanicoDental("", cod_usuario_telar_mecanico_dental);
            $("div[id=divAbmMecanicoDental1]").fadeIn(250);
            // Oculta el listado
            $("div[id=divAbmMecanicoDental2]").fadeOut(250);
        } else {
            buscarMecanicosDentales();
            $("div[id=divAbmMecanicoDental2]").fadeIn(250);
        }
    } else {
        if (mostrarAbm) {
            $("div[id=divAbmMecanicoDental1]").fadeOut(250);
            // Muestra el listado de vuelta
            $("div[id=divAbmMecanicoDental2]").fadeIn(250);
        } else {
            $("div[id=divAbmMecanicoDental2]").fadeOut(250);
            $("div[id=divAbmMecanicoDental]").fadeOut(250);
        }
    }
}

function actualizarEstadoCuentaTelarMecanicoDental(mensaje, esError) {
    var estado = document.getElementById("estadoUsuarioTelarMecanicoDental");
    if (!estado) {
        return;
    }
    estado.textContent = mensaje || "";
    estado.style.color = esError ? "#b42318" : "#526473";
}

function programarBusquedaCuentaTelarMecanicoDental(busqueda) {
    if (temporizador_busqueda_usuario_telar_mecanico) {
        clearTimeout(temporizador_busqueda_usuario_telar_mecanico);
    }
    temporizador_busqueda_usuario_telar_mecanico = setTimeout(function () {
        var selector = document.getElementById("inptUsuarioTelarAbmMecanicoDental");
        var seleccionado = selector ? selector.value : cod_usuario_telar_mecanico_dental;
        cargarCuentasTelarMecanicoDental(busqueda, seleccionado);
    }, 250);
}

function cargarCuentasTelarMecanicoDental(busqueda, usuarioSeleccionado) {
    var selector = document.getElementById("inptUsuarioTelarAbmMecanicoDental");
    var buscador = document.getElementById("inptBuscarUsuarioTelarMecanicoDental");
    if (!selector || !buscador) {
        return;
    }

    usuarioSeleccionado = usuarioSeleccionado || "";
    var secuenciaActual = ++secuencia_busqueda_usuario_telar_mecanico;
    actualizarEstadoCuentaTelarMecanicoDental("Buscando cuentas Telar activas y disponibles...", false);
    obtener_datos_user();
    var datos = new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "buscarUsuariosTelar");
    datos.append("busqueda", busqueda || "");
    datos.append("cod_mecanico_dental", cod_mecanico_dental || "");
    datos.append("cod_usuario_actual", usuarioSeleccionado);

    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmMecanicoDental.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        error: function () {
            if (secuenciaActual !== secuencia_busqueda_usuario_telar_mecanico) {
                return;
            }
            actualizarEstadoCuentaTelarMecanicoDental("No se pudieron consultar las cuentas Telar.", true);
        },
        success: function (responseText) {
            if (secuenciaActual !== secuencia_busqueda_usuario_telar_mecanico) {
                return;
            }
            try {
                var respuesta = $.parseJSON(responseText);
                if (respuesta["1"] !== "exito") {
                    selector.disabled = true;
                    buscador.disabled = true;
                    actualizarEstadoCuentaTelarMecanicoDental(respuesta.mensaje || "No se pudieron consultar las cuentas Telar.", true);
                    return;
                }

                selector.innerHTML = "";
                var opcionVacia = document.createElement("option");
                opcionVacia.value = "";
                opcionVacia.textContent = "Sin cuenta vinculada";
                selector.appendChild(opcionVacia);

                var cuentas = respuesta.cuentas || [];
                for (var i = 0; i < cuentas.length; i++) {
                    var cuenta = cuentas[i];
                    var opcion = document.createElement("option");
                    opcion.value = String(cuenta.cod_usuario);
                    opcion.textContent = cuenta.nombre_persona
                        + (cuenta.login ? " (" + cuenta.login + ")" : "")
                        + (cuenta.activa ? "" : " - CUENTA INACTIVA");
                    selector.appendChild(opcion);
                }

                selector.disabled = respuesta.disponible !== true;
                buscador.disabled = respuesta.disponible !== true;
                selector.value = usuarioSeleccionado;
                if (selector.value !== String(usuarioSeleccionado || "")) {
                    selector.value = "";
                }
                cod_usuario_telar_mecanico_dental = selector.value;
                actualizarEstadoCuentaTelarMecanicoDental(respuesta.mensaje || "", respuesta.disponible !== true);
            } catch (error) {
                actualizarEstadoCuentaTelarMecanicoDental("La respuesta de cuentas Telar no pudo ser interpretada.", true);
            }
        }
    });
}

function verificarCamposMecanicoDental() {
    const nombre= $("#inptNombreMecanicoAbmMecanicoDental").val();
    const telefono= $("#inptTelefonoAbmMecanicoDental").val();
    const direccion= $("#inptDireccionAbmMecanicoDental").val();
    const telefono_referencia= $("#inptTelefonoReferenciaAbmMecanicoDental").val();
    const estado= $("#inptEstadoAbmMecanicoDental").val();
    const cuenta_telar= $("#inptUsuarioTelarAbmMecanicoDental").val() || "";

    if (!nombre || nombre == "") {
        ver_vetana_informativa("Falta completar el nombre");
        return false;
    }
    if (!telefono || telefono == "") {
        ver_vetana_informativa("Falta completar el numero de telefono");
        return false;
    }
    
    abmMecanicoDental(nombre, telefono, direccion, telefono_referencia,estado,cuenta_telar);
}

function abmMecanicoDental(nombre, telefono, direccion, telefono_referencia,estado,cuenta_telar) {
    obtener_datos_user();
    var datos = new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);

    // Recopilar datos del formulario
    datos.append("cod_mecanico_dental", cod_mecanico_dental);
    datos.append("nombre", nombre);
    datos.append("direccion", direccion);
    datos.append("telefono", telefono);
    datos.append("estado", estado);
    datos.append("telefono_referencia", telefono_referencia);
    datos.append("cod_personaFK", cod_persona);
    datos.append("cod_usuarioFK", cuenta_telar || "");
    datos.append("accion", "nuevo/editar");
    
    verCerrarEfectoCargando("1");
    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmMecanicoDental.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        xhr: function () {
            var xhr = new window.XMLHttpRequest();
            //Uload progress
            xhr.upload.addEventListener("progress", function (evt) {
                var kb = ((evt.loaded * 1) / 1000).toFixed(1);
                if (kb == "0.0") { kb = 0.1; }
            }, false);
            //Download progress
            xhr.addEventListener("progress", function (evt) {
                var kb = ((evt.loaded * 1) / 1000).toFixed(1);
                if (kb == "0.0") { kb = 0.1; }
            }, false);
            return xhr;
        },
        error: function (jqXHR, textstatus, errorThrowm) {
            verCerrarEfectoCargando("");
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
            ver_vetana_informativa("Error de conexión al procesar la solicitud.");
        },
        success: function (responseText) {
            Respuesta = responseText;
            console.log(Respuesta);
            try {
                var datos = $.parseJSON(Respuesta);
                if (datos["1"] === "exito") {
                    ver_vetana_informativa("Datos guardados.", "", "info");
                    limpiarFormularioMecanicoDental();
                    verCerrarVentanaMecanicoDental(false, true);
                    buscarMecanicosDentales(); // Recargar la lista
                } else if (datos["1"] === "UI") {
                    ver_vetana_informativa("Usuario o contraseña incorrectos.");
                } else {
                    ver_vetana_informativa("Ha ocurrido un error: " + (datos["mensaje"] || ""));
                }
            } catch (error) {
                ver_vetana_informativa("Error inesperado",  "Lo sentimos, ha ocurrido un error", "error");
                var titulo = "Error: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
            } finally {
                verCerrarEfectoCargando("");
            }
        }
    });
}

function buscarOpcionesMecanicoDental() {
    obtener_datos_user();
    let datos = new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "buscarVistaOpciones");
    
    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmMecanicoDental.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        error: function (jqXHR, textstatus, errorThrowm) {
            verCerrarEfectoCargando("");
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
            ver_vetana_informativa("Error de conexión al buscar registros.");
        },
        success: function (responseText) {
            verCerrarEfectoCargando("");
            console.log(responseText);
            document.getElementById('inptMecanicoTrabajoMecanicoDental').innerHTML= "";
            try {
                var datos = $.parseJSON(responseText);
                if (datos["1"] === "exito") {
                    document.getElementById('inptMecanicoTrabajoMecanicoDental').innerHTML= "<option value=''>Seleccionar</option>" + datos["2"];
                }
            } catch (error) {
                ver_vetana_informativa("Error inesperado",  "Lo sentimos, ha ocurrido un error", "error");
                var titulo = "Error: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
            }
        }
    });
}

function buscarMecanicosDentales() {
    const codigo= document.getElementById('inptCodListadoMecanicoDental').value;
    const nombre= document.getElementById('inptNombreListadoMecanicoDental').value;
    const estado= document.getElementById('inptEstadoListadoMecanicoDental').value;
    obtener_datos_user();
    let datos = new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "buscarVista");
    datos.append("cod_mecanico_dental", codigo);
    datos.append("nombre", nombre);
    datos.append("estado", estado);
    
    verCerrarEfectoCargando("1");
    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmMecanicoDental.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        error: function (jqXHR, textstatus, errorThrowm) {
            verCerrarEfectoCargando("");
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
            ver_vetana_informativa("Error de conexión al buscar registros.");
            document.getElementById('tablaMecanicoDental').innerHTML= "";
        },
        success: function (responseText) {
            verCerrarEfectoCargando("");
            console.log(responseText);
            document.getElementById('tablaMecanicoDental').innerHTML= "";
            try {
                var datos = $.parseJSON(responseText);
                if (datos["1"] === "exito") {
                    document.getElementById('tablaMecanicoDental').innerHTML= datos["2"];
                    document.getElementById('inptRegistroNroMecanicoDenta').value= datos["3"];
                }
            } catch (error) {
                ver_vetana_informativa("Error inesperado",  "Lo sentimos, ha ocurrido un error", "error");
                var titulo = "Error: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
            }
        }
    });
}

function limpiarFormularioMecanicoDental() {
    cod_mecanico_dental = "";
    cod_persona = "";
    cod_usuario_telar_mecanico_dental = "";
    document.getElementById('inptNombreMecanicoAbmMecanicoDental').value = "";
    document.getElementById('inptTelefonoAbmMecanicoDental').value = "";
    document.getElementById('inptDireccionAbmMecanicoDental').value = "";
    document.getElementById('inptTelefonoReferenciaAbmMecanicoDental').value = "";
    document.getElementById('inptEstadoAbmMecanicoDental').value = "activo";
    if (document.getElementById('inptBuscarUsuarioTelarMecanicoDental')) {
        document.getElementById('inptBuscarUsuarioTelarMecanicoDental').value = "";
    }
    if (document.getElementById('inptUsuarioTelarAbmMecanicoDental')) {
        document.getElementById('inptUsuarioTelarAbmMecanicoDental').value = "";
    }
    document.getElementById('inptRegistroSeleccMecanicoDental').value= ""
    document.getElementById('btnEditarMecanicoDental').style.backgroundColor= "#b7b7b7";
    document.getElementById('btnEditarMecanicoDental').disabled= true;
}

function ObtenerdatosMecanicoDental(elemento) {
    cod_mecanico_dental = $(elemento).children('td[id="td_id"]').html();
    cod_persona = $(elemento).children('td[id="td_datos_6"]').html();
    cod_usuario_telar_mecanico_dental = $.trim($(elemento).children('td[id="td_datos_8"]').text());
    document.getElementById('inptNombreMecanicoAbmMecanicoDental').value = $(elemento).children('td[id="td_datos_1"]').html();
    document.getElementById('inptTelefonoAbmMecanicoDental').value = $(elemento).children('td[id="td_datos_3"]').html();
    document.getElementById('inptDireccionAbmMecanicoDental').value = $(elemento).children('td[id="td_datos_5"]').html();
    document.getElementById('inptTelefonoReferenciaAbmMecanicoDental').value = $(elemento).children('td[id="td_datos_4"]').html();
    document.getElementById('inptEstadoAbmMecanicoDental').value = $(elemento).children('td[id="td_datos_2"]').html().toLowerCase();
    document.getElementById('inptRegistroSeleccMecanicoDental').value = $(elemento).children('td[id="td_id"]').html();
    if (document.getElementById('inptBuscarUsuarioTelarMecanicoDental')) {
        document.getElementById('inptBuscarUsuarioTelarMecanicoDental').value = "";
    }
    cargarCuentasTelarMecanicoDental("", cod_usuario_telar_mecanico_dental);
    
    $("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	elemento.className = 'tableRegistroSelec'
    document.getElementById('btnEditarMecanicoDental').style.backgroundColor= "#416c8f";
    document.getElementById('btnEditarMecanicoDental').disabled= false;
}

function minimizarVentanaMecanicoDental() {
    document.getElementById('divAbmMecanicoDental').style.display= 'none';
    document.getElementById('divMinimizadoMecanicoDental').style.display= '';
}

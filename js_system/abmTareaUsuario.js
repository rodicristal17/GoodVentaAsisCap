/*
ABM Tareas Programadas
Adaptado al HTML nuevo:
- Nombre
- Hora
- Tipo
*/

var idAbmTareaProgramada = "";

function obtenerValorTareaProgramada(id) {
    var elemento = document.getElementById(id);

    if (!elemento) {
        return "";
    }

    return elemento.value;
}

function setValorTareaProgramada(id, valor) {
    var elemento = document.getElementById(id);

    if (!elemento) {
        return;
    }

    elemento.value = valor;
}

function setHtmlTareaProgramada(id, valor) {
    var elemento = document.getElementById(id);

    if (!elemento) {
        return;
    }

    elemento.innerHTML = valor;
}

function setDisplayTareaProgramada(id, valor) {
    var elemento = document.getElementById(id);

    if (!elemento) {
        return;
    }

    elemento.style.display = valor;
}

function obtenerTextoTdTareaProgramada(datostr, idTd) {
    var td = $(datostr).children('td[id="' + idTd + '"]');

    if (td.length == 0) {
        return "";
    }

    return $.trim(td.html());
}

function normalizarHoraTareaProgramada(valor) {
    if (valor == null || valor == "") {
        return "";
    }

    valor = String(valor).trim();

    /*
        Convierte:
        08:30:00
        a:
        08:30
    */
    if (valor.length >= 5) {
        valor = valor.substring(0, 5);
    }

    return valor;
}

function verCerrarAbmTareaProgramada() {

    setDisplayTareaProgramada("divSegundoPlano", "none");

    if (document.getElementById("divAbmTareaProgramada").style.display == "") {

        setDisplayTareaProgramada("divMinimizadoListadoDeTareasProgramadas", "none");

        limpiarCamposBuscarTareaProgramada();
        limpiarcamposTareaProgramada();

        $("div[id=divAbmTareaProgramada]").fadeOut(500);

    } else {

        // if (controlacceso("VERFORMULARIOTAREAPROGRAMADA", "accion") == false) {
            // return;
        // }

        document.getElementById("divAbmTareaProgramada").style.display = "";
    }
}

function limpiarCamposBuscarTareaProgramada() {

    setValorTareaProgramada("inptBuscarAbmTareaProgramada1", "");
    setValorTareaProgramada("inptBuscarAbmTareaProgramada2", "");
    setValorTareaProgramada("inptBuscarAbmTareaProgramada3", "");
    setValorTareaProgramada("inptBuscarAbmTareaProgramada6", "");

    setValorTareaProgramada("inptTotalRegistoTareaProgramada", "");
    setValorTareaProgramada("inptRegistroSeleccTareaProgramada", "");

    setHtmlTareaProgramada("table_abm_TareaProgramada", "");

    setDisplayTareaProgramada("overlayFiltrosTareaProgramada", "none");
}

function verCerrarFiltrosTareaProgramada(mostrar) {

    if (mostrar) {
        setDisplayTareaProgramada("overlayFiltrosTareaProgramada", "");
    } else {
        setDisplayTareaProgramada("overlayFiltrosTareaProgramada", "none");
    }
}

function limpiarFiltroTareaProgramada() {

    setValorTareaProgramada("inptBuscarAbmTareaProgramada1", "");
    setValorTareaProgramada("inptBuscarAbmTareaProgramada2", "");
    setValorTareaProgramada("inptBuscarAbmTareaProgramada3", "");
    setValorTareaProgramada("inptBuscarAbmTareaProgramada6", "");

    buscarabmTareaProgramada();
}

function minimizarTareaProgramada() {

    setDisplayTareaProgramada("divMinimizadoListadoDeTareasProgramadas", "");

    $("div[id=divAbmTareaProgramada]").fadeOut(500);
}

function verCerrarVentanaAbmTareaProgramada(d, l) {

    if (d == "1") {

        if (l == "1") {

            // if (controlacceso("INSERTARFORMULARIOTAREAPROGRAMADA", "accion") == false) {
                // return;
            // }

            limpiarcamposTareaProgramada();
        }

        $("div[id=divAbmTareaProgramada2]").fadeIn(250);

        document.getElementById("divAbmTareaProgramada1").style.display = "none";

    } else {

        $("div[id=divAbmTareaProgramada1]").fadeIn(250);

        document.getElementById("divAbmTareaProgramada2").style.display = "none";
    }
}

function verVentanaEditarTareaProgramada() {

    // if (controlacceso("EDITARFORMULARIOTAREAPROGRAMADA", "accion") == false) {
        // return;
    // }

    if (idAbmTareaProgramada == "") {
        ver_vetana_informativa("FALTO SELECCIONAR UN REGISTRO");
        return;
    }

    verCerrarVentanaAbmTareaProgramada("1", "2");
}

function obtenerdatosabmTareaProgramada(datostr) {

    $("tr[id=tbSelecRegistro]").each(function(i, td) {
        td.className = "";
    });

    datostr.className = "tableRegistroSelec";

    var id = obtenerTextoTdTareaProgramada(datostr, "td_id");
    var nombre = obtenerTextoTdTareaProgramada(datostr, "td_datos_1");
    var hora = obtenerTextoTdTareaProgramada(datostr, "td_datos_2");
    var tipo = obtenerTextoTdTareaProgramada(datostr, "td_datos_3");

    setValorTareaProgramada("inptIdTareaProgramada", id);
    setValorTareaProgramada("inptNombreTareaProgramada", nombre);
    setValorTareaProgramada("inptHoraTareaProgramada", normalizarHoraTareaProgramada(hora));
    setValorTareaProgramada("inptTipoTareaProgramada", tipo);
    setValorTareaProgramada("inptRegistroSeleccTareaProgramada", nombre);

    if (document.getElementById("btnAbmTareaProgramada")) {
        document.getElementById("btnAbmTareaProgramada").value = "Editar datos";
    }

    idAbmTareaProgramada = id;
}

function verificarcamposTareaProgramada() {

    var nombre = obtenerValorTareaProgramada("inptNombreTareaProgramada");
    var hora = obtenerValorTareaProgramada("inptHoraTareaProgramada");
    var tipo = obtenerValorTareaProgramada("inptTipoTareaProgramada");

    if (nombre == "") {
        ver_vetana_informativa("FALTO INGRESAR EL NOMBRE DE LA TAREA");
        return false;
    }

    if (hora == "") {
        ver_vetana_informativa("FALTO INGRESAR LA HORA");
        return false;
    }

    if (tipo == "") {
        ver_vetana_informativa("FALTO SELECCIONAR EL TIPO");
        return false;
    }

    var accion = "";

    if (idAbmTareaProgramada != "") {

        accion = "editar";

        // if (controlacceso("EDITARFORMULARIOTAREAPROGRAMADA", "accion") == false) {
            // return;
        // }

    } else {

        accion = "nuevo";

        // if (controlacceso("INSERTARFORMULARIOTAREAPROGRAMADA", "accion") == false) {
            // return;
        // }
    }

    abmTareaProgramada(
        idAbmTareaProgramada,
        nombre,
        hora,
        tipo,
        accion
    );
}

function abmTareaProgramada(id, nombre, hora, tipo, accion) {

    verCerrarEfectoCargando("1");

    var datos = new FormData();

    obtener_datos_user();

    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);

    datos.append("funt", accion);
    datos.append("id", id);
    datos.append("nombre", nombre);
    datos.append("hora", hora);
    datos.append("tipo", tipo);

    /*
        Estos campos no están en tu HTML actual.
        Los mando por defecto por si tu PHP los necesita para insertar.
    */
    datos.append("estado", "pendiente");
    datos.append("cod_usuarioFK", userid);

    var OpAjax = $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmTareaProgramada.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,

        xhr: function() {

            var xhr = new window.XMLHttpRequest();

            xhr.upload.addEventListener("progress", function(evt) {

                if (evt.lengthComputable) {

                    var porce = ~~((evt.loaded / evt.total) * 100);

                    if (porce > 90) {
                        porce = Number(porce) - 7;
                    }

                    if (document.getElementById("lbltitulomensaje_b")) {
                        document.getElementById("lbltitulomensaje_b").innerHTML = "Cargando<br>(" + porce + "%)";
                    }
                }

                var kb = ((evt.loaded * 1) / 1000).toFixed(1);

                if (kb == "0.0") {
                    kb = 0.1;
                }

                cargarConectividad("enviado", kb, "0");

            }, false);

            xhr.addEventListener("progress", function(evt) {

                var kb = ((evt.loaded * 1) / 1000).toFixed(1);

                if (kb == "0.0") {
                    kb = 0.1;
                }

                cargarConectividad("recibido", "0", kb);

            }, false);

            return xhr;
        },

        error: function(jqXHR, textstatus, errorThrowm) {

            verCerrarEfectoCargando("");

            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");

            return false;
        },

        success: function(responseText) {

            verCerrarEfectoCargando("");

            var Respuesta = responseText;

            console.log(Respuesta);

            try {

                var datos = $.parseJSON(Respuesta);

                Respuesta = datos["1"];

                Respuesta = respuestaJqueryAjax(Respuesta);

                if (Respuesta == true) {

                    limpiarcamposTareaProgramada();

                    ver_vetana_informativa("DATOS CARGADOS CORRECTAMENTE...");

                    idAbmTareaProgramada = "";

                    buscarabmTareaProgramada();

                    verCerrarVentanaAbmTareaProgramada("2", "");
                }

            } catch (error) {

                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR");

                var titulo = "Error: " + error + " \r\n Consola: " + responseText;

                GuardarArchivosLog(titulo);
            }
        }
    });
}

function buscarabmTareaProgramada() {

    // if (controlacceso("BUSCARFORMULARIOTAREAPROGRAMADA", "accion") == false) {
        // return;
    // }

    var codigo = obtenerValorTareaProgramada("inptBuscarAbmTareaProgramada1");
    var nombre = obtenerValorTareaProgramada("inptBuscarAbmTareaProgramada2");
    var hora = obtenerValorTareaProgramada("inptBuscarAbmTareaProgramada3");
    var tipo = obtenerValorTareaProgramada("inptBuscarAbmTareaProgramada6");

    setHtmlTareaProgramada("table_abm_TareaProgramada", paginacargando);

    obtener_datos_user();

    var datos = {
        "useru": userid,
        "passu": passuser,
        "navegador": navegador,
        "codigo": codigo,
        "nombre": nombre,
        "hora": hora,
        "tipo": tipo,
        "funt": "buscar"
    };

    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmTareaProgramada.php",
        type: "post",

        xhr: function() {

            var xhr = new window.XMLHttpRequest();

            xhr.upload.addEventListener("progress", function(evt) {

                var kb = ((evt.loaded * 1) / 1000).toFixed(1);

                if (kb == "0.0") {
                    kb = 0.1;
                }

                cargarConectividad("enviado", kb, "0");

            }, false);

            xhr.addEventListener("progress", function(evt) {

                var kb = ((evt.loaded * 1) / 1000).toFixed(1);

                if (kb == "0.0") {
                    kb = 0.1;
                }

                cargarConectividad("recibido", "0", kb);

            }, false);

            return xhr;
        },

        error: function(jqXHR, textstatus, errorThrowm) {

            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");

            setHtmlTareaProgramada("table_abm_TareaProgramada", "");
        },

        success: function(responseText) {

            var Respuesta = responseText;

            console.log(Respuesta);

            setHtmlTareaProgramada("table_abm_TareaProgramada", "");

            try {

                var datos = $.parseJSON(Respuesta);

                Respuesta = datos["1"];

                Respuesta = respuestaJqueryAjax(Respuesta);

                if (Respuesta == true) {

                    var datos_buscados = datos[2];

                    setHtmlTareaProgramada("table_abm_TareaProgramada", datos_buscados);

                    setValorTareaProgramada("inptTotalRegistoTareaProgramada", datos[3]);
                }

            } catch (error) {

                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR");

                var titulo = "Error: " + error + " \r\n Consola: " + responseText;

                GuardarArchivosLog(titulo);
            }
        }
    });
}

function limpiarcamposTareaProgramada() {

    setValorTareaProgramada("inptIdTareaProgramada", "0");
    setValorTareaProgramada("inptNombreTareaProgramada", "");
    setValorTareaProgramada("inptHoraTareaProgramada", "");
    setValorTareaProgramada("inptTipoTareaProgramada", "DIARIO");
    setValorTareaProgramada("inptRegistroSeleccTareaProgramada", "");

    if (document.getElementById("btnAbmTareaProgramada")) {
        document.getElementById("btnAbmTareaProgramada").value = "Guardar datos";
    }

    idAbmTareaProgramada = "";
}





var codUsuarioSeleccionadoTarea = "";
var nombreUsuarioSeleccionadoTarea = "";
var fotoUsuarioSeleccionadoTarea = "";

function verCerrarVentanaAsignarTareaUsuario(mostrar) {
    if (mostrar) {
        document.getElementById("divFrmAsignarTareaUsuario").style.display = "";
        buscarUsuariosAsignarTarea();
    } else {
        document.getElementById("divFrmAsignarTareaUsuario").style.display = "none";
    }
}

function limpiarFiltrosAsignarTareaUsuario() {
    document.getElementById("inptBuscarUsuarioAsignarTarea").value = "";
    document.getElementById("inptBuscarTipoUsuarioAsignarTarea").value = "";
    document.getElementById("inptBuscarEstadoUsuarioAsignarTarea").value = "Activo";

    buscarUsuariosAsignarTarea();
}

function seleccionarUsuarioAsignarTarea(codUsuario, nombreUsuario, fotoUsuario) {
    codUsuarioSeleccionadoTarea = codUsuario;
    nombreUsuarioSeleccionadoTarea = nombreUsuario;
    fotoUsuarioSeleccionadoTarea = fotoUsuario;

    document.getElementById("inptCodUsuarioAsignadoTarea").value = codUsuario;
    document.getElementById("inptFotoUsuarioAsignadoTarea").value = fotoUsuario;

    document.getElementById("lblUsuarioSeleccionadoTarea").innerHTML = nombreUsuario;
    document.getElementById("imgUsuarioSeleccionadoTarea").src = fotoUsuario;

    document.getElementById("boxUsuarioSeleccionadoTarea").style.display = "";

    var cards = document.getElementsByClassName("asignar-tarea__card");

    for (var i = 0; i < cards.length; i++) {
        cards[i].classList.remove("asignar-tarea__card--activo");
    }

    var cardSeleccionado = document.getElementById("usuarioAsignarTarea_" + codUsuario);

    if (cardSeleccionado) {
        cardSeleccionado.classList.add("asignar-tarea__card--activo");
    }
}

function confirmarUsuarioAsignadoTarea() {
    if (codUsuarioSeleccionadoTarea == "") {
        ver_vetana_informativa("FALTO SELECCIONAR UN USUARIO");
        return;
    }

    if (document.getElementById("inptUsuarioTareaProgramada")) {
        document.getElementById("inptUsuarioTareaProgramada").value = codUsuarioSeleccionadoTarea;
    }

    if (document.getElementById("lblUsuarioAsignadoTareaProgramada")) {
        document.getElementById("lblUsuarioAsignadoTareaProgramada").innerHTML = nombreUsuarioSeleccionadoTarea;
    }

    verCerrarModalTareasParaAsignar(true);
    buscarTareasParaAsignarUsuario();
}

function buscarUsuariosAsignarTarea() {
    var buscar = document.getElementById("inptBuscarUsuarioAsignarTarea").value;
    var tipo = document.getElementById("inptBuscarTipoUsuarioAsignarTarea").value;
    var estado = document.getElementById("inptBuscarEstadoUsuarioAsignarTarea").value;

    document.getElementById("contenedorUsuariosAsignarTarea").innerHTML = paginacargando;

    obtener_datos_user();

    var datos = {
        "useru": userid,
        "passu": passuser,
        "navegador": navegador,
        "buscar": buscar,
        "tipo": tipo,
        "estado": estado,
        "funt": "buscarUsuariosAsignarTarea"
    };

    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmTareaProgramada.php",
        type: "post",

        error: function(jqXHR, textstatus, errorThrowm) {
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
            document.getElementById("contenedorUsuariosAsignarTarea").innerHTML = "";
        },

        success: function(responseText) {
            var Respuesta = responseText;

            console.log(Respuesta);

            try {
                var datos = $.parseJSON(Respuesta);

                Respuesta = datos["1"];
                Respuesta = respuestaJqueryAjax(Respuesta);

                if (Respuesta == true) {
                    document.getElementById("contenedorUsuariosAsignarTarea").innerHTML = datos[2];
                }

            } catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR");

                var titulo = "Error: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
            }
        }
    });
}



var codTareaSeleccionadaAsignar = "";
var nombreTareaSeleccionadaAsignar = "";

function verCerrarModalTareasParaAsignar(mostrar) {
    if (mostrar) {
        document.getElementById("modalTareasParaAsignarUsuario").style.display = "";

        if (document.getElementById("lblUsuarioTareaModal")) {
            document.getElementById("lblUsuarioTareaModal").innerHTML = nombreUsuarioSeleccionadoTarea;
        }

        codTareaSeleccionadaAsignar = "";
        nombreTareaSeleccionadaAsignar = "";

        if (document.getElementById("inptCodTareaSeleccionadaAsignar")) {
            document.getElementById("inptCodTareaSeleccionadaAsignar").value = "";
        }

        if (document.getElementById("boxTareaSeleccionadaAsignar")) {
            document.getElementById("boxTareaSeleccionadaAsignar").style.display = "none";
        }

        if (document.getElementById("inptFechaTareaAsignada")) {
            var hoy = new Date();
            var yyyy = hoy.getFullYear();
            var mm = String(hoy.getMonth() + 1).padStart(2, "0");
            var dd = String(hoy.getDate()).padStart(2, "0");

            document.getElementById("inptFechaTareaAsignada").value = yyyy + "-" + mm + "-" + dd;
        }

    } else {
        document.getElementById("modalTareasParaAsignarUsuario").style.display = "none";
    }
}

function seleccionarTareaParaAsignar(elemento) {
    var codTarea = elemento.getAttribute("data-id");
    var nombreTarea = elemento.getAttribute("data-nombre");

    codTareaSeleccionadaAsignar = codTarea;
    nombreTareaSeleccionadaAsignar = nombreTarea;

    document.getElementById("inptCodTareaSeleccionadaAsignar").value = codTarea;
    document.getElementById("lblTareaSeleccionadaAsignar").innerHTML = nombreTarea;
    document.getElementById("boxTareaSeleccionadaAsignar").style.display = "";

    var cards = document.getElementsByClassName("asignar-tarea-modal__card");

    for (var i = 0; i < cards.length; i++) {
        cards[i].classList.remove("asignar-tarea-modal__card--activo");
    }

    elemento.classList.add("asignar-tarea-modal__card--activo");
}

function buscarTareasParaAsignarUsuario() {
    if (codUsuarioSeleccionadoTarea == "") {
        ver_vetana_informativa("FALTO SELECCIONAR UN USUARIO");
        return;
    }

    var buscar = "";
    var tipo = "";
    var estado = "";

    if (document.getElementById("inptBuscarTareaParaAsignarUsuario")) {
        buscar = document.getElementById("inptBuscarTareaParaAsignarUsuario").value;
    }

    if (document.getElementById("inptBuscarTipoTareaParaAsignarUsuario")) {
        tipo = document.getElementById("inptBuscarTipoTareaParaAsignarUsuario").value;
    }

    if (document.getElementById("inptBuscarEstadoTareaParaAsignarUsuario")) {
        estado = document.getElementById("inptBuscarEstadoTareaParaAsignarUsuario").value;
    }

    document.getElementById("contenedorTareasParaAsignarUsuario").innerHTML = paginacargando;

    obtener_datos_user();

    var datos = {
        "useru": userid,
        "passu": passuser,
        "navegador": navegador,
        "funt": "buscarTareasParaAsignarUsuario",
        "buscar": buscar,
        "tipo": tipo,
        "estado": estado,
        "cod_usuario": codUsuarioSeleccionadoTarea
    };

    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmTareaProgramada.php",
        type: "post",

        error: function(jqXHR, textstatus, errorThrowm) {
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
            document.getElementById("contenedorTareasParaAsignarUsuario").innerHTML = "";
        },

        success: function(responseText) {
            var Respuesta = responseText;

            console.log(Respuesta);

            try {
                var datos = $.parseJSON(Respuesta);

                Respuesta = datos["1"];
                Respuesta = respuestaJqueryAjax(Respuesta);

                if (Respuesta == true) {
                    document.getElementById("contenedorTareasParaAsignarUsuario").innerHTML = datos[2];
                }

            } catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR");

                var titulo = "Error: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
            }
        }
    });
}

function asignarTareaSeleccionadaAUsuario() {
    if (codUsuarioSeleccionadoTarea == "") {
        ver_vetana_informativa("FALTO SELECCIONAR UN USUARIO");
        return;
    }

    if (codTareaSeleccionadaAsignar == "") {
        ver_vetana_informativa("FALTO SELECCIONAR UNA TAREA");
        return;
    }

    var fechaTarea = "";

    if (document.getElementById("inptFechaTareaAsignada")) {
        fechaTarea = document.getElementById("inptFechaTareaAsignada").value;
    }

    if (fechaTarea == "") {
        ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE LA TAREA");
        return;
    }

    verCerrarEfectoCargando("1");

    obtener_datos_user();

    var datos = {
        "useru": userid,
        "passu": passuser,
        "navegador": navegador,
        "funt": "asignarTareaAUsuario",
        "id_tarea": codTareaSeleccionadaAsignar,
        "cod_usuario": codUsuarioSeleccionadoTarea,
        "fecha_tarea": fechaTarea
    };

    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmTareaProgramada.php",
        type: "post",

        error: function(jqXHR, textstatus, errorThrowm) {
            verCerrarEfectoCargando("");
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
        },

        success: function(responseText) {
            verCerrarEfectoCargando("");

            var Respuesta = responseText;

            console.log(Respuesta);

            try {
                var datos = $.parseJSON(Respuesta);

                if (datos["1"] == "duplicado") {
                    ver_vetana_informativa(datos["mensaje"]);
                    return;
                }

                Respuesta = datos["1"];
                Respuesta = respuestaJqueryAjax(Respuesta);

                if (Respuesta == true) {
                    ver_vetana_informativa("TAREA ASIGNADA CORRECTAMENTE");

                    // verCerrarModalTareasParaAsignar(false);
                    // verCerrarVentanaAsignarTareaUsuario(false);

                    codTareaSeleccionadaAsignar = "";
                    nombreTareaSeleccionadaAsignar = "";

                    if (typeof buscarabmTareaProgramada === "function") {
                        buscarabmTareaProgramada();
                    }
					buscarTareasParaAsignarUsuario()
					if (document.getElementById("boxTareaSeleccionadaAsignar")) {
						document.getElementById("boxTareaSeleccionadaAsignar").style.display = "none";
					}
                    if (typeof cargarTareasPendientesAdministrador === "function") {
                        cargarTareasPendientesAdministrador();
                    }
                }

            } catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR");

                var titulo = "Error: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
            }
        }
    });
}


function cargarTareasPendientesAdministrador() {

    var lista = document.getElementById("listaTareasAdministrador");
    var vacio = document.getElementById("tareasAdministradorVacio");
    var contador = document.getElementById("spanTareasPendientes");
    var contadorPendientesResumen = document.getElementById("spanTareasPendientesResumen");
    var contadorProceso = document.getElementById("spanTareasEnProceso");
    var contadorAtrasadas = document.getElementById("spanTareasAtrasadas");
    var contadorCompletadas = document.getElementById("spanTareasCompletadas");

    if (!lista) {
        return;
    }

    lista.innerHTML = paginacargando;

    if (vacio) {
        vacio.style.display = "none";
    }

    if (contador) {
        contador.innerHTML = "0";
    }

    if (contadorPendientesResumen) {
        contadorPendientesResumen.innerHTML = "0";
    }

    if (contadorProceso) {
        contadorProceso.innerHTML = "0";
    }

    if (contadorAtrasadas) {
        contadorAtrasadas.innerHTML = "0";
    }

    if (contadorCompletadas) {
        contadorCompletadas.innerHTML = "0";
    }

    obtener_datos_user();

    var datos = {
        "useru": userid,
        "passu": passuser,
        "navegador": navegador,
        "cod_usuario": userid,
        "funt": "buscarTareasPendientesAdministrador"
    };

    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmTareaProgramada.php",
        type: "post",

        error: function(jqXHR, textstatus, errorThrowm) {
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");

            lista.innerHTML = "";

            if (vacio) {
                vacio.style.display = "";
            }
        },

        success: function(responseText) {

            var Respuesta = responseText;

            console.log("TAREAS PENDIENTES:", Respuesta);

            try {

                var datos = $.parseJSON(Respuesta);

                Respuesta = datos["1"];
                Respuesta = respuestaJqueryAjax(Respuesta);

                if (Respuesta == true) {

					var html = datos[2];
					var totalPendientes = datos[3];
					var totalTareas = datos[4];
					var totalProceso = datos[5] || 0;
					var totalCompletadas = datos[6] || 0;
					var totalAtrasadas = datos[8] || 0;

					lista.innerHTML = html;

					if (contador) {
                        contador.innerHTML = totalPendientes;

                        var contenedorContador = contador.parentNode;

                        if (contenedorContador) {
                            if (parseInt(totalPendientes) > 0) {
                                contenedorContador.classList.add("perfil-tareas__contador--alerta");
                            } else {
                                contenedorContador.classList.remove("perfil-tareas__contador--alerta");
                            }
                        }
                    }

                    if (contadorPendientesResumen) {
                        contadorPendientesResumen.innerHTML = totalPendientes;
                    }

                    if (contadorProceso) {
                        contadorProceso.innerHTML = totalProceso;
                    }

                    if (contadorAtrasadas) {
                        contadorAtrasadas.innerHTML = totalAtrasadas;
                    }

                    if (contadorCompletadas) {
                        contadorCompletadas.innerHTML = totalCompletadas;
                    }

					if (vacio) {
						if (parseInt(totalTareas) > 0) {
							vacio.style.display = "none";
						} else {
							vacio.style.display = "";
						}
					}
				}

            } catch (error) {

                lista.innerHTML = "";

                if (vacio) {
                    vacio.style.display = "";
                }

                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR");

                var titulo = "Error: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
            }
        }
    });
}


function cambiarEstadoTareaAsignada(check, codTareaAsignada) {

    if (!check || !codTareaAsignada) {
        return;
    }

    var estadoNuevo = "";

    if (check.checked == true) {
        estadoNuevo = "Completada";
    } else {
        estadoNuevo = "Pendiente";
    }

    obtener_datos_user();

    var datos = {
        "useru": userid,
        "passu": passuser,
        "navegador": navegador,
        "funt": "cambiarEstadoTareaAsignada",
        "cod_tarea_asignada": codTareaAsignada,
        "estado_tarea": estadoNuevo
    };

    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmTareaProgramada.php",
        type: "post",

        error: function(jqXHR, textstatus, errorThrowm) {
            check.checked = !check.checked;
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
        },

        success: function(responseText) {

            var Respuesta = responseText;

            console.log("CAMBIAR ESTADO TAREA:", Respuesta);

            try {

                var datos = $.parseJSON(Respuesta);

                Respuesta = datos["1"];
                Respuesta = respuestaJqueryAjax(Respuesta);

                if (Respuesta == true) {

                    if (estadoNuevo == "Completada") {
                        ver_vetana_informativa("TAREA COMPLETADA CORRECTAMENTE");
                    }

                    if (typeof cargarTareasPendientesAdministrador === "function") {
                        cargarTareasPendientesAdministrador();
                    }

                } else {
                    check.checked = !check.checked;
                }

            } catch (error) {

                check.checked = !check.checked;

                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR");

                var titulo = "Error: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
            }
        }
    });
}


var codTareaDiariaSeleccionada = "";
var nombreTareaDiariaSeleccionada = "";

function confirmarUsuarioAsignadoTareaDiaria() {
    if (codUsuarioSeleccionadoTarea == "") {
        ver_vetana_informativa("FALTO SELECCIONAR UN USUARIO");
        return;
    }

    verCerrarModalTareaDiaria(true);
    buscarTareasParaAsignarDiariaUsuario();
}

function verCerrarModalTareaDiaria(mostrar) {
    if (mostrar) {

        document.getElementById("modalAsignarTareaDiaria").style.display = "";

        if (document.getElementById("lblUsuarioTareaDiariaModal")) {
            document.getElementById("lblUsuarioTareaDiariaModal").innerHTML = nombreUsuarioSeleccionadoTarea;
        }

        codTareaDiariaSeleccionada = "";
        nombreTareaDiariaSeleccionada = "";

        if (document.getElementById("inptCodTareaDiariaSeleccionada")) {
            document.getElementById("inptCodTareaDiariaSeleccionada").value = "";
        }

        if (document.getElementById("boxTareaDiariaSeleccionada")) {
            document.getElementById("boxTareaDiariaSeleccionada").style.display = "none";
        }

        setFechaActualTareaDiaria();

    } else {
        document.getElementById("modalAsignarTareaDiaria").style.display = "none";
    }
}

function setFechaActualTareaDiaria() {
    var hoy = new Date();
    var yyyy = hoy.getFullYear();
    var mm = String(hoy.getMonth() + 1).padStart(2, "0");
    var dd = String(hoy.getDate()).padStart(2, "0");
    var fechaActual = yyyy + "-" + mm + "-" + dd;

    if (document.getElementById("inptFechaInicioTareaDiaria")) {
        document.getElementById("inptFechaInicioTareaDiaria").value = fechaActual;
    }

    if (document.getElementById("inptFechaFinTareaDiaria")) {
        document.getElementById("inptFechaFinTareaDiaria").value = "";
    }
}

function seleccionarTareaDiariaParaAsignar(elemento) {
    var codTarea = elemento.getAttribute("data-id");
    var nombreTarea = elemento.getAttribute("data-nombre");

    codTareaDiariaSeleccionada = codTarea;
    nombreTareaDiariaSeleccionada = nombreTarea;

    document.getElementById("inptCodTareaDiariaSeleccionada").value = codTarea;
    document.getElementById("lblTareaDiariaSeleccionada").innerHTML = nombreTarea;
    document.getElementById("boxTareaDiariaSeleccionada").style.display = "";

    var cards = document.getElementsByClassName("tarea-diaria-modal__card");

    for (var i = 0; i < cards.length; i++) {
        cards[i].classList.remove("tarea-diaria-modal__card--activo");
    }

    elemento.classList.add("tarea-diaria-modal__card--activo");
}

function valorCheckTareaDiaria(id) {
    if (!document.getElementById(id)) {
        return "No";
    }

    if (document.getElementById(id).checked == true) {
        return "Si";
    }

    return "No";
}

function buscarTareasParaAsignarDiariaUsuario() {
    if (codUsuarioSeleccionadoTarea == "") {
        ver_vetana_informativa("FALTO SELECCIONAR UN USUARIO");
        return;
    }

    var buscar = "";
    var tipo = "";

    if (document.getElementById("inptBuscarTareaDiaria")) {
        buscar = document.getElementById("inptBuscarTareaDiaria").value;
    }

    if (document.getElementById("inptBuscarTipoTareaDiaria")) {
        tipo = document.getElementById("inptBuscarTipoTareaDiaria").value;
    }

    document.getElementById("contenedorTareasDiariasParaAsignar").innerHTML = paginacargando;

    obtener_datos_user();

    var datos = {
        "useru": userid,
        "passu": passuser,
        "navegador": navegador,
        "funt": "buscarTareasParaAsignarDiariaUsuario",
        "buscar": buscar,
        "tipo": tipo,
        "cod_usuario": codUsuarioSeleccionadoTarea
    };

    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmTareaProgramada.php",
        type: "post",

        error: function(jqXHR, textstatus, errorThrowm) {
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
            document.getElementById("contenedorTareasDiariasParaAsignar").innerHTML = "";
        },

        success: function(responseText) {
            var Respuesta = responseText;

            console.log("TAREAS DIARIAS:", Respuesta);

            try {
                var datos = $.parseJSON(Respuesta);

                Respuesta = datos["1"];
                Respuesta = respuestaJqueryAjax(Respuesta);

                if (Respuesta == true) {
                    document.getElementById("contenedorTareasDiariasParaAsignar").innerHTML = datos[2];
                }

            } catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR");

                var titulo = "Error: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
            }
        }
    });
}

function guardarTareaDiariaUsuario() {
    if (codUsuarioSeleccionadoTarea == "") {
        ver_vetana_informativa("FALTO SELECCIONAR UN USUARIO");
        return;
    }

    if (codTareaDiariaSeleccionada == "") {
        ver_vetana_informativa("FALTO SELECCIONAR UNA TAREA");
        return;
    }

    var fechaInicio = "";
    var fechaFin = "";
    var observacion = "";

    if (document.getElementById("inptFechaInicioTareaDiaria")) {
        fechaInicio = document.getElementById("inptFechaInicioTareaDiaria").value;
    }

    if (document.getElementById("inptFechaFinTareaDiaria")) {
        fechaFin = document.getElementById("inptFechaFinTareaDiaria").value;
    }

    if (document.getElementById("inptObservacionTareaDiaria")) {
        observacion = document.getElementById("inptObservacionTareaDiaria").value;
    }

    if (fechaInicio == "") {
        ver_vetana_informativa("FALTO SELECCIONAR LA FECHA DE INICIO");
        return;
    }

    var lunes = valorCheckTareaDiaria("checkTareaDiariaLunes");
    var martes = valorCheckTareaDiaria("checkTareaDiariaMartes");
    var miercoles = valorCheckTareaDiaria("checkTareaDiariaMiercoles");
    var jueves = valorCheckTareaDiaria("checkTareaDiariaJueves");
    var viernes = valorCheckTareaDiaria("checkTareaDiariaViernes");
    var sabado = valorCheckTareaDiaria("checkTareaDiariaSabado");
    var domingo = valorCheckTareaDiaria("checkTareaDiariaDomingo");

    if (
        lunes == "No" &&
        martes == "No" &&
        miercoles == "No" &&
        jueves == "No" &&
        viernes == "No" &&
        sabado == "No" &&
        domingo == "No"
    ) {
        ver_vetana_informativa("DEBE SELECCIONAR AL MENOS UN DÍA");
        return;
    }

    verCerrarEfectoCargando("1");

    obtener_datos_user();

    var datos = {
        "useru": userid,
        "passu": passuser,
        "navegador": navegador,
        "funt": "guardarTareaDiariaUsuario",
        "cod_tareaFK": codTareaDiariaSeleccionada,
        "cod_usuarioFK": codUsuarioSeleccionadoTarea,
        "fecha_inicio": fechaInicio,
        "fecha_fin": fechaFin,
        "lunes": lunes,
        "martes": martes,
        "miercoles": miercoles,
        "jueves": jueves,
        "viernes": viernes,
        "sabado": sabado,
        "domingo": domingo,
        "observacion_admin": observacion
    };

    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmTareaProgramada.php",
        type: "post",

        error: function(jqXHR, textstatus, errorThrowm) {
            verCerrarEfectoCargando("");
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
        },

        success: function(responseText) {
            verCerrarEfectoCargando("");

            var Respuesta = responseText;

            console.log("GUARDAR TAREA DIARIA:", Respuesta);

            try {
                var datos = $.parseJSON(Respuesta);

                if (datos["1"] == "duplicado") {
                    ver_vetana_informativa(datos["mensaje"]);
                    return;
                }

                Respuesta = datos["1"];
                Respuesta = respuestaJqueryAjax(Respuesta);

                if (Respuesta == true) {
                    ver_vetana_informativa("TAREA DIARIA GUARDADA CORRECTAMENTE");

                    verCerrarModalTareaDiaria(false);
                    verCerrarVentanaAsignarTareaUsuario(false);

                    codTareaDiariaSeleccionada = "";
                    nombreTareaDiariaSeleccionada = "";
                }

            } catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR");

                var titulo = "Error: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
            }
        }
    });
}

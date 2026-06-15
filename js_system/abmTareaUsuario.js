/*
ABM Tareas Programadas
Adaptado al HTML nuevo:
- Nombre
- Hora
- Tipo
*/

var idAbmTareaProgramada = "";
var tipoDestinoAsignarTarea = "USUARIO";
var rolDestinoAsignarTarea = "";

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
var vistaAsignarTarea = "USUARIOS";
var agendaDiaTareasEstado = {
    solicitud: 0,
    cargando: false,
    tieneRespuesta: false,
    ultimoHtml: ""
};

function obtenerValorCampoTareaPersonal(id) {
    var elemento = document.getElementById(id);

    if (!elemento) {
        return "";
    }

    return elemento.value || "";
}

function setValorCampoTareaPersonal(id, valor) {
    var elemento = document.getElementById(id);

    if (elemento) {
        elemento.value = valor;
    }
}

function obtenerHoraActualTareaPersonal() {
    var fecha = new Date();
    return String(fecha.getHours()).padStart(2, "0") + ":" + String(fecha.getMinutes()).padStart(2, "0");
}

function crearEstadoAgendaDia(tipo, mensaje, accion) {
    var clase = "perfil-tareas__estado-panel perfil-tareas__estado-panel--" + tipo;
    var html = "<div class='" + clase + "'>";

    if (tipo == "cargando") {
        html += "<div class='perfil-tareas__skeleton'></div><div class='perfil-tareas__skeleton perfil-tareas__skeleton--short'></div>";
    }

    html += "<strong>" + mensaje + "</strong>";

    if (accion) {
        html += accion;
    }

    html += "</div>";
    return html;
}

function mostrarEstadoAgendaVacia() {
    return crearEstadoAgendaDia(
        "vacio",
        "Todavia no hay tareas cargadas para hoy.",
        "<span>Cuando administracion o tu rol generen actividades, apareceran aca ordenadas por hora. Tambien podes agregar una tarea rapida.</span>" +
        "<button type='button' onclick='verFormularioTareaRapidaAgenda(true)'>+ Agregar tarea rapida</button>" +
        "<small>Si crees que deberian aparecer tareas fijas, comunicate con administracion.</small>"
    );
}

function mostrarEstadoAgendaError() {
    return crearEstadoAgendaDia(
        "error",
        "No pudimos cargar tus tareas. Intenta nuevamente.",
        "<button type='button' onclick='cargarTareasPendientesAdministrador({forzarEstado:true})'>Reintentar</button>"
    );
}

function mostrarEstadoAgendaCargando() {
    return crearEstadoAgendaDia("cargando", "Cargando agenda del dia...", "");
}

function setAgendaDiaActualizando(actualizando) {
    var contenedor = document.getElementById("divTareasAdministrador");
    var indicador = document.getElementById("spanAgendaDiaActualizando");

    if (contenedor) {
        contenedor.classList.toggle("perfil-tareas--actualizando", actualizando === true);
    }

    if (indicador) {
        indicador.style.display = actualizando === true ? "" : "none";
    }
}

function setResumenAgendaDia(totalPendientes, totalProceso, totalAtrasadas, totalCompletadas, totalTareas) {
    var contador = document.getElementById("spanTareasPendientes");
    var contadorLabel = document.getElementById("spanTareasPendientesLabel");
    var btnRapidoHeader = document.getElementById("btnAgendaRapidaHeader");
    var contadorPendientesResumen = document.getElementById("spanTareasPendientesResumen");
    var contadorProceso = document.getElementById("spanTareasEnProceso");
    var contadorAtrasadas = document.getElementById("spanTareasAtrasadas");
    var contadorCompletadas = document.getElementById("spanTareasCompletadas");
    var resumen = document.querySelector("#divTareasAdministrador .perfil-tareas__resumen");

    var totalTareasNumero = parseInt(totalTareas, 10) || 0;
    var totalCompletadasNumero = parseInt(totalCompletadas, 10) || 0;
    var porcentajeCompletadas = totalTareasNumero > 0 ? Math.round((totalCompletadasNumero * 100) / totalTareasNumero) : 0;

    if (contador) {
        contador.innerHTML = totalTareasNumero > 0 ? (porcentajeCompletadas + "%") : "Sin tareas";
    }

    if (contadorLabel) {
        contadorLabel.innerHTML = totalTareasNumero > 0 ? "completadas" : "para hoy";
    }

    if (btnRapidoHeader) {
        btnRapidoHeader.style.display = totalTareasNumero > 0 ? "" : "none";
    }

    var contenedorContador = contador ? contador.parentNode : null;
    if (contenedorContador) {
        contenedorContador.classList.toggle("perfil-tareas__contador--alerta", totalTareasNumero > 0 && (parseInt(totalPendientes, 10) > 0 || parseInt(totalAtrasadas, 10) > 0));
        contenedorContador.classList.toggle("perfil-tareas__contador--empty", totalTareasNumero == 0);
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

    if (resumen) {
        resumen.style.display = totalTareasNumero > 0 ? "grid" : "none";
    }
}

function verFormularioTareaRapidaAgenda(mostrar) {
    var form = document.getElementById("formTareaRapidaAgenda");

    if (!form) {
        return;
    }

    form.style.display = mostrar ? "" : "none";

    if (mostrar) {
        setValorCampoTareaPersonal("inptHoraTareaRapidaAgenda", obtenerHoraActualTareaPersonal());
        setTimeout(function() {
            var titulo = document.getElementById("inptTituloTareaRapidaAgenda");
            if (titulo) {
                titulo.focus();
            }
        }, 80);
    }
}

function usarHoraActualTareaRapidaAgenda() {
    setValorCampoTareaPersonal("inptHoraTareaRapidaAgenda", obtenerHoraActualTareaPersonal());
}

function crearTareaPersonalizada(datosTarea, alFinalizar) {
    obtener_datos_user();

    var esRol = datosTarea.tipo_destino == "ROL";
    var datos = {
        "useru": userid,
        "passu": passuser,
        "navegador": navegador,
        "funt": esRol ? "crearTareaRapidaRol" : "crearTareaRapidaUsuario",
        "titulo": datosTarea.titulo || "",
        "hora": datosTarea.hora || "",
        "tipo_tarea": datosTarea.tipo_tarea || "RAPIDA",
        "prioridad": datosTarea.prioridad || "Normal",
        "comentario": datosTarea.comentario || "",
        "cod_usuario_destino": datosTarea.cod_usuario_destino || userid,
        "rol_operativo": datosTarea.rol_operativo || "",
        "tipo_destino": datosTarea.tipo_destino || "USUARIO",
        "origen": datosTarea.origen || "funcionario"
    };

    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmTareaProgramada.php",
        type: "post",

        error: function(jqXHR, textstatus, errorThrowm) {
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
            if (typeof alFinalizar == "function") {
                alFinalizar(false);
            }
        },

        success: function(responseText) {
            var Respuesta = responseText;
            console.log("CREAR TAREA PERSONAL:", Respuesta);

            try {
                var datosRespuesta = $.parseJSON(Respuesta);
                Respuesta = datosRespuesta["1"];
                Respuesta = respuestaJqueryAjax(Respuesta);

                if (Respuesta == true) {
                    if (typeof alFinalizar == "function") {
                        alFinalizar(true, datosRespuesta);
                    }
                    return;
                }

                if (typeof alFinalizar == "function") {
                    alFinalizar(false, datosRespuesta);
                }

            } catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR");
                var titulo = "Error: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
                if (typeof alFinalizar == "function") {
                    alFinalizar(false);
                }
            }
        }
    });
}

function guardarTareaRapidaAgenda() {
    var titulo = obtenerValorCampoTareaPersonal("inptTituloTareaRapidaAgenda").trim();

    if (titulo == "") {
        ver_vetana_informativa("FALTO INGRESAR EL TITULO DE LA TAREA");
        return;
    }

    var btn = document.getElementById("btnGuardarTareaRapidaAgenda");
    if (btn) {
        btn.disabled = true;
    }

    crearTareaPersonalizada({
        titulo: titulo,
        hora: obtenerValorCampoTareaPersonal("inptHoraTareaRapidaAgenda"),
        tipo_tarea: "RAPIDA",
        prioridad: obtenerValorCampoTareaPersonal("inptPrioridadTareaRapidaAgenda"),
        comentario: obtenerValorCampoTareaPersonal("inptComentarioTareaRapidaAgenda"),
        cod_usuario_destino: userid,
        origen: "funcionario"
    }, function(exito) {
        if (btn) {
            btn.disabled = false;
        }

        if (exito) {
            setValorCampoTareaPersonal("inptTituloTareaRapidaAgenda", "");
            setValorCampoTareaPersonal("inptComentarioTareaRapidaAgenda", "");
            verFormularioTareaRapidaAgenda(false);
            cargarTareasPendientesAdministrador({forzarEstado:true});
            ver_vetana_informativa("TAREA RAPIDA AGREGADA");
        }
    });
}

function verFormularioTareaFuncionario(codUsuario, mostrar) {
    var form = document.getElementById("formAgregarTareaFuncionario_" + codUsuario);

    if (!form) {
        return;
    }

    form.style.display = mostrar ? "" : "none";

    if (mostrar) {
        setValorCampoTareaPersonal("inptHoraTareaFuncionario_" + codUsuario, obtenerHoraActualTareaPersonal());
        setTimeout(function() {
            var titulo = document.getElementById("inptTituloTareaFuncionario_" + codUsuario);
            if (titulo) {
                titulo.focus();
            }
        }, 80);
    }
}

function guardarTareaRapidaGestion(codUsuario) {
    var titulo = obtenerValorCampoTareaPersonal("inptTituloTareaFuncionario_" + codUsuario).trim();

    if (titulo == "") {
        ver_vetana_informativa("FALTO INGRESAR EL TITULO DE LA TAREA");
        return;
    }

    crearTareaPersonalizada({
        titulo: titulo,
        hora: obtenerValorCampoTareaPersonal("inptHoraTareaFuncionario_" + codUsuario),
        tipo_tarea: obtenerValorCampoTareaPersonal("inptTipoTareaFuncionario_" + codUsuario) || "CASUAL",
        prioridad: obtenerValorCampoTareaPersonal("inptPrioridadTareaFuncionario_" + codUsuario),
        comentario: obtenerValorCampoTareaPersonal("inptComentarioTareaFuncionario_" + codUsuario),
        cod_usuario_destino: codUsuario,
        origen: "administracion"
    }, function(exito) {
        if (exito) {
            verFormularioTareaFuncionario(codUsuario, false);
            buscarUsuariosAsignarTarea({
                mantenerSeleccion: true,
                expandidos: [String(codUsuario)]
            });
            ver_vetana_informativa("TAREA AGREGADA CORRECTAMENTE");
        }
    });
}

function verFormularioTareaRol(idRol, mostrar) {
    var form = document.getElementById("formAgregarTareaRol_" + idRol);

    if (!form) {
        return;
    }

    form.style.display = mostrar ? "" : "none";

    if (mostrar) {
        setValorCampoTareaPersonal("inptHoraTareaRol_" + idRol, obtenerHoraActualTareaPersonal());
        setTimeout(function() {
            var titulo = document.getElementById("inptTituloTareaRol_" + idRol);
            if (titulo) {
                titulo.focus();
            }
        }, 80);
    }
}

function guardarTareaRapidaRolGestion(idRol, rolOperativo) {
    var titulo = obtenerValorCampoTareaPersonal("inptTituloTareaRol_" + idRol).trim();

    if (titulo == "") {
        ver_vetana_informativa("FALTO INGRESAR EL TITULO DE LA TAREA");
        return;
    }

    crearTareaPersonalizada({
        titulo: titulo,
        hora: obtenerValorCampoTareaPersonal("inptHoraTareaRol_" + idRol),
        tipo_tarea: obtenerValorCampoTareaPersonal("inptTipoTareaRol_" + idRol) || "CASUAL",
        prioridad: obtenerValorCampoTareaPersonal("inptPrioridadTareaRol_" + idRol),
        comentario: obtenerValorCampoTareaPersonal("inptComentarioTareaRol_" + idRol),
        tipo_destino: "ROL",
        rol_operativo: rolOperativo,
        origen: "administracion"
    }, function(exito, datosRespuesta) {
        if (exito) {
            verFormularioTareaRol(idRol, false);
            buscarRolesAsignarTarea({
                expandidos: [String(idRol)]
            });

            var mensaje = "TAREA AGREGADA AL ROL";
            if (datosRespuesta && datosRespuesta.insertados) {
                mensaje += ". Usuarios vinculados: " + datosRespuesta.insertados;
            }
            ver_vetana_informativa(mensaje);
        } else if (datosRespuesta && datosRespuesta.mensaje) {
            ver_vetana_informativa(datosRespuesta.mensaje);
        }
    });
}

function obtenerRolesExpandidosAsignarTarea() {
    var expandidos = [];
    var detalles = document.querySelectorAll(".asignar-tarea__role-detail");

    for (var i = 0; i < detalles.length; i++) {
        if (detalles[i].style.display != "none") {
            expandidos.push(detalles[i].id.replace("detalleRolTarea_", ""));
        }
    }

    return expandidos;
}

function restaurarRolesExpandidosAsignarTarea(expandidos) {
    if (!expandidos || !expandidos.length) {
        return;
    }

    for (var i = 0; i < expandidos.length; i++) {
        var detalle = document.getElementById("detalleRolTarea_" + expandidos[i]);
        var card = document.getElementById("rolAsignarTarea_" + expandidos[i]);

        if (detalle) {
            detalle.style.display = "";
        }

        if (card) {
            card.classList.add("asignar-tarea__role-card--abierto");
            card.classList.add("asignar-tarea__card--activo");
        }
    }
}

function toggleRolAsignarTarea(evento, idRol) {
    if (evento && evento.stopPropagation) {
        evento.stopPropagation();
    }

    var detalle = document.getElementById("detalleRolTarea_" + idRol);
    var card = document.getElementById("rolAsignarTarea_" + idRol);

    if (!detalle || !card) {
        return;
    }

    var abrir = detalle.style.display == "none";
    detalle.style.display = abrir ? "" : "none";
    card.classList.toggle("asignar-tarea__role-card--abierto", abrir);
}

function obtenerFuncionariosExpandidosAsignarTarea() {
    var expandidos = [];
    var detalles = document.querySelectorAll(".asignar-tarea__funcionario-detail");

    for (var i = 0; i < detalles.length; i++) {
        if (detalles[i].style.display != "none") {
            expandidos.push(detalles[i].id.replace("detalleFuncionarioTarea_", ""));
        }
    }

    return expandidos;
}

function restaurarFuncionariosExpandidosAsignarTarea(expandidos) {
    if (!expandidos || !expandidos.length) {
        return;
    }

    for (var i = 0; i < expandidos.length; i++) {
        var detalle = document.getElementById("detalleFuncionarioTarea_" + expandidos[i]);
        var card = document.getElementById("usuarioAsignarTarea_" + expandidos[i]);

        if (detalle) {
            detalle.style.display = "";
        }

        if (card) {
            card.classList.add("asignar-tarea__funcionario-card--abierto");
        }
    }
}

function toggleFuncionarioAsignarTarea(evento, codUsuario) {
    if (evento && evento.stopPropagation) {
        evento.stopPropagation();
    }

    var detalle = document.getElementById("detalleFuncionarioTarea_" + codUsuario);
    var card = document.getElementById("usuarioAsignarTarea_" + codUsuario);

    if (!detalle || !card) {
        return;
    }

    var abrir = detalle.style.display == "none";
    detalle.style.display = abrir ? "" : "none";
    card.classList.toggle("asignar-tarea__funcionario-card--abierto", abrir);
}

function marcarTareaGestionDiaria(control, codTarea, codUsuarioResponsable) {
    cambiarEstadoTareaAsignada(control, codTarea, codUsuarioResponsable);
}

function resetSeleccionAsignarTarea(mantenerDestino) {
    codUsuarioSeleccionadoTarea = "";
    nombreUsuarioSeleccionadoTarea = "";
    fotoUsuarioSeleccionadoTarea = "";

    if (mantenerDestino !== true) {
        tipoDestinoAsignarTarea = "USUARIO";
        rolDestinoAsignarTarea = "";
    }

    if (document.getElementById("inptCodUsuarioAsignadoTarea")) {
        document.getElementById("inptCodUsuarioAsignadoTarea").value = "";
    }

    if (document.getElementById("inptFotoUsuarioAsignadoTarea")) {
        document.getElementById("inptFotoUsuarioAsignadoTarea").value = "";
    }

    if (document.getElementById("boxUsuarioSeleccionadoTarea")) {
        document.getElementById("boxUsuarioSeleccionadoTarea").style.display = "none";
    }

    if (document.getElementById("boxUsuarioSinSeleccionTarea")) {
        document.getElementById("boxUsuarioSinSeleccionTarea").style.display = "";
    }

    var cards = document.getElementsByClassName("asignar-tarea__card");

    for (var i = 0; i < cards.length; i++) {
        cards[i].classList.remove("asignar-tarea__card--activo");
    }
}

function setTextoAsignarTarea(id, texto) {
    if (document.getElementById(id)) {
        document.getElementById(id).innerHTML = texto;
    }
}

function buscarVistaAsignarTarea() {
    if (vistaAsignarTarea == "ROLES") {
        buscarRolesAsignarTarea();
    } else {
        buscarUsuariosAsignarTarea();
    }
}

function cambiarVistaAsignarTarea(vista) {
    vistaAsignarTarea = vista == "ROLES" ? "ROLES" : "USUARIOS";

    if (document.getElementById("btnVistaAsignarUsuarios")) {
        document.getElementById("btnVistaAsignarUsuarios").classList.toggle("asignar-tarea__tab--activo", vistaAsignarTarea == "USUARIOS");
    }

    if (document.getElementById("btnVistaAsignarRoles")) {
        document.getElementById("btnVistaAsignarRoles").classList.toggle("asignar-tarea__tab--activo", vistaAsignarTarea == "ROLES");
    }

    resetSeleccionAsignarTarea();

    if (vistaAsignarTarea == "ROLES") {
        setTextoAsignarTarea("kickerPanelAsignarTarea", "Roles");
        setTextoAsignarTarea("tituloPanelAsignarTarea", "Roles operativos");
        setTextoAsignarTarea("notaPanelAsignarTarea", "Seleccion&aacute; un rol para preparar la asignaci&oacute;n.");
        setTextoAsignarTarea("boxUsuarioSinSeleccionTarea", "<strong>Seleccion&aacute; un rol</strong><span>Ac&aacute; vas a ver sus usuarios activos, tareas pendientes y tareas configuradas.</span>");
        buscarRolesAsignarTarea();
    } else {
        setTextoAsignarTarea("kickerPanelAsignarTarea", "Usuarios");
        setTextoAsignarTarea("tituloPanelAsignarTarea", "Personal disponible");
        setTextoAsignarTarea("notaPanelAsignarTarea", "Hac&eacute; clic en un funcionario para desplegar sus tareas.");
        setTextoAsignarTarea("boxUsuarioSinSeleccionTarea", "<strong>Seleccion&aacute; un funcionario</strong><span>Ac&aacute; vas a ver su local, rol operativo, horario y resumen de tareas del d&iacute;a.</span>");
        buscarUsuariosAsignarTarea();
    }
}

function verCerrarVentanaAsignarTareaUsuario(mostrar) {
    if (mostrar) {
        document.getElementById("divFrmAsignarTareaUsuario").style.display = "";
        cambiarVistaAsignarTarea(vistaAsignarTarea);
    } else {
        document.getElementById("divFrmAsignarTareaUsuario").style.display = "none";
    }
}

function limpiarFiltrosAsignarTareaUsuario() {
    document.getElementById("inptBuscarUsuarioAsignarTarea").value = "";
    document.getElementById("inptBuscarTipoUsuarioAsignarTarea").value = "";
    if (document.getElementById("inptBuscarRolOperativoAsignarTarea")) {
        document.getElementById("inptBuscarRolOperativoAsignarTarea").value = "";
    }
    document.getElementById("inptBuscarEstadoUsuarioAsignarTarea").value = "Activo";

    resetSeleccionAsignarTarea();
    buscarVistaAsignarTarea();
}

function seleccionarUsuarioAsignarTarea(codUsuario, nombreUsuario, fotoUsuario, ciUsuario, localUsuario, rolUsuario, horarioUsuario, tareasPendientes, tareasCompletadas) {
    codUsuarioSeleccionadoTarea = codUsuario;
    nombreUsuarioSeleccionadoTarea = nombreUsuario;
    fotoUsuarioSeleccionadoTarea = fotoUsuario;
    tipoDestinoAsignarTarea = "USUARIO";
    rolDestinoAsignarTarea = "";

    document.getElementById("inptCodUsuarioAsignadoTarea").value = codUsuario;
    document.getElementById("inptFotoUsuarioAsignadoTarea").value = fotoUsuario;

    document.getElementById("lblUsuarioSeleccionadoTarea").innerHTML = nombreUsuario;
    document.getElementById("imgUsuarioSeleccionadoTarea").src = fotoUsuario;

    if (document.getElementById("lblTipoSeleccionAsignarTarea")) {
        document.getElementById("lblTipoSeleccionAsignarTarea").innerHTML = "Usuario seleccionado";
    }

    if (document.getElementById("lblUsuarioSeleccionadoCI")) {
        document.getElementById("lblUsuarioSeleccionadoCI").innerHTML = ciUsuario ? "CI: " + ciUsuario : "";
    }

    if (document.getElementById("lblUsuarioSeleccionadoLocal")) {
        document.getElementById("lblUsuarioSeleccionadoLocal").innerHTML = localUsuario || "Sin definir";
    }

    if (document.getElementById("lblUsuarioSeleccionadoRol")) {
        document.getElementById("lblUsuarioSeleccionadoRol").innerHTML = rolUsuario || "Sin definir";
    }

    if (document.getElementById("lblUsuarioSeleccionadoHorario")) {
        document.getElementById("lblUsuarioSeleccionadoHorario").innerHTML = horarioUsuario || "No configurado";
    }

    if (document.getElementById("lblUsuarioPendientesTarea")) {
        document.getElementById("lblUsuarioPendientesTarea").innerHTML = tareasPendientes || "0";
    }

    if (document.getElementById("lblUsuarioCompletadasTarea")) {
        document.getElementById("lblUsuarioCompletadasTarea").innerHTML = tareasCompletadas || "0";
    }

    if (document.getElementById("boxUsuarioSeleccionadoTarea")) {
        document.getElementById("boxUsuarioSeleccionadoTarea").style.display = "none";
    }

    if (document.getElementById("boxUsuarioSinSeleccionTarea")) {
        document.getElementById("boxUsuarioSinSeleccionTarea").style.display = "none";
    }

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

    tipoDestinoAsignarTarea = "USUARIO";
    rolDestinoAsignarTarea = "";

    if (document.getElementById("inptUsuarioTareaProgramada")) {
        document.getElementById("inptUsuarioTareaProgramada").value = codUsuarioSeleccionadoTarea;
    }

    if (document.getElementById("lblUsuarioAsignadoTareaProgramada")) {
        document.getElementById("lblUsuarioAsignadoTareaProgramada").innerHTML = nombreUsuarioSeleccionadoTarea;
    }

    verCerrarModalTareasParaAsignar(true);
    buscarTareasParaAsignarUsuario();
}

function confirmarRolAsignadoTarea() {
    var rolOperativo = "";

    if (rolDestinoAsignarTarea != "") {
        rolOperativo = rolDestinoAsignarTarea;
    } else if (document.getElementById("inptBuscarRolOperativoAsignarTarea")) {
        rolOperativo = document.getElementById("inptBuscarRolOperativoAsignarTarea").value;
    }

    rolOperativo = (rolOperativo || "").trim();

    if (rolOperativo == "") {
        ver_vetana_informativa("FALTO INDICAR EL ROL OPERATIVO");
        return;
    }

    tipoDestinoAsignarTarea = "ROL";
    rolDestinoAsignarTarea = rolOperativo;
    nombreUsuarioSeleccionadoTarea = "Rol: " + rolOperativo;

    verCerrarModalTareasParaAsignar(true);
    buscarTareasParaAsignarUsuario();
}

function seleccionarRolAsignarTarea(rolOperativo, totalUsuarios, usuariosActivos, usuariosInactivos, tareasPendientes, tareasCompletadas, tareasHoy, tareasDiarias) {
    codUsuarioSeleccionadoTarea = "";
    nombreUsuarioSeleccionadoTarea = "Rol: " + rolOperativo;
    fotoUsuarioSeleccionadoTarea = "/GoodVentaAsisCap/iconos/usuariosacceso.png";
    tipoDestinoAsignarTarea = "ROL";
    rolDestinoAsignarTarea = rolOperativo;

    if (document.getElementById("inptCodUsuarioAsignadoTarea")) {
        document.getElementById("inptCodUsuarioAsignadoTarea").value = "";
    }

    if (document.getElementById("inptFotoUsuarioAsignadoTarea")) {
        document.getElementById("inptFotoUsuarioAsignadoTarea").value = fotoUsuarioSeleccionadoTarea;
    }

    if (document.getElementById("lblUsuarioSeleccionadoTarea")) {
        document.getElementById("lblUsuarioSeleccionadoTarea").innerHTML = rolOperativo;
    }

    if (document.getElementById("lblTipoSeleccionAsignarTarea")) {
        document.getElementById("lblTipoSeleccionAsignarTarea").innerHTML = "Rol seleccionado";
    }

    if (document.getElementById("imgUsuarioSeleccionadoTarea")) {
        document.getElementById("imgUsuarioSeleccionadoTarea").src = fotoUsuarioSeleccionadoTarea;
    }

    if (document.getElementById("lblUsuarioSeleccionadoCI")) {
        document.getElementById("lblUsuarioSeleccionadoCI").innerHTML = "Usuarios activos: " + (usuariosActivos || "0") + " / Total: " + (totalUsuarios || "0");
    }

    if (document.getElementById("lblUsuarioSeleccionadoLocal")) {
        document.getElementById("lblUsuarioSeleccionadoLocal").innerHTML = "Todos los locales";
    }

    if (document.getElementById("lblUsuarioSeleccionadoRol")) {
        document.getElementById("lblUsuarioSeleccionadoRol").innerHTML = rolOperativo || "Sin definir";
    }

    if (document.getElementById("lblUsuarioSeleccionadoHorario")) {
        document.getElementById("lblUsuarioSeleccionadoHorario").innerHTML = tareasDiarias || "Sin tareas diarias configuradas";
    }

    if (document.getElementById("lblUsuarioPendientesTarea")) {
        document.getElementById("lblUsuarioPendientesTarea").innerHTML = tareasPendientes || "0";
    }

    if (document.getElementById("lblUsuarioCompletadasTarea")) {
        document.getElementById("lblUsuarioCompletadasTarea").innerHTML = tareasCompletadas || "0";
    }

    if (document.getElementById("boxUsuarioSeleccionadoTarea")) {
        document.getElementById("boxUsuarioSeleccionadoTarea").style.display = "none";
    }

    if (document.getElementById("boxUsuarioSinSeleccionTarea")) {
        document.getElementById("boxUsuarioSinSeleccionTarea").style.display = "none";
    }

    var cards = document.getElementsByClassName("asignar-tarea__card");

    for (var i = 0; i < cards.length; i++) {
        cards[i].classList.remove("asignar-tarea__card--activo");

        if (cards[i].getAttribute("data-rol") == rolOperativo) {
            cards[i].classList.add("asignar-tarea__card--activo");
        }
    }
}

function buscarRolesAsignarTarea(opciones) {
    opciones = opciones || {};

    var buscar = document.getElementById("inptBuscarUsuarioAsignarTarea").value;
    var rolOperativo = "";
    var estado = document.getElementById("inptBuscarEstadoUsuarioAsignarTarea").value;
    var contenedor = document.getElementById("contenedorUsuariosAsignarTarea");
    var expandidos = opciones.expandidos || obtenerRolesExpandidosAsignarTarea();

    if (document.getElementById("inptBuscarRolOperativoAsignarTarea")) {
        rolOperativo = document.getElementById("inptBuscarRolOperativoAsignarTarea").value;
    }

    if (rolOperativo != "") {
        buscar = rolOperativo;
    }

    resetSeleccionAsignarTarea(true);
    tipoDestinoAsignarTarea = "ROL";

    if (contenedor) {
        if ($.trim(contenedor.innerHTML) == "" || contenedor.querySelector(".asignar-tarea__vacio")) {
            contenedor.innerHTML = paginacargando;
        } else {
            contenedor.classList.add("asignar-tarea__contenedor--actualizando");
        }
    }

    obtener_datos_user();

    var datos = {
        "useru": userid,
        "passu": passuser,
        "navegador": navegador,
        "buscar": buscar,
        "estado": estado,
        "funt": "buscarRolesAsignarTarea"
    };

    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmTareaProgramada.php",
        type: "post",

        error: function(jqXHR, textstatus, errorThrowm) {
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
            if (contenedor) {
                contenedor.classList.remove("asignar-tarea__contenedor--actualizando");
                if ($.trim(contenedor.innerHTML) == "" || contenedor.innerHTML == paginacargando) {
                    contenedor.innerHTML = "<div class='asignar-tarea__vacio'><p>No pudimos cargar los roles. Intenta nuevamente.</p></div>";
                }
            }
        },

        success: function(responseText) {
            var Respuesta = responseText;

            console.log(Respuesta);

            try {
                var datos = $.parseJSON(Respuesta);

                Respuesta = datos["1"];
                Respuesta = respuestaJqueryAjax(Respuesta);

                if (Respuesta == true) {
                    if (contenedor) {
                        contenedor.innerHTML = datos[2];
                        contenedor.classList.remove("asignar-tarea__contenedor--actualizando");
                        restaurarRolesExpandidosAsignarTarea(expandidos);
                    }
                }

            } catch (error) {
                if (contenedor) {
                    contenedor.classList.remove("asignar-tarea__contenedor--actualizando");
                }
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR");

                var titulo = "Error: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
            }
        }
    });
}

function buscarUsuariosAsignarTarea(opciones) {
    opciones = opciones || {};

    var buscar = document.getElementById("inptBuscarUsuarioAsignarTarea").value;
    var tipo = document.getElementById("inptBuscarTipoUsuarioAsignarTarea").value;
    var estado = document.getElementById("inptBuscarEstadoUsuarioAsignarTarea").value;
    var rolOperativo = "";
    var contenedor = document.getElementById("contenedorUsuariosAsignarTarea");
    var expandidos = opciones.expandidos || obtenerFuncionariosExpandidosAsignarTarea();

    if (document.getElementById("inptBuscarRolOperativoAsignarTarea")) {
        rolOperativo = document.getElementById("inptBuscarRolOperativoAsignarTarea").value;
    }

    if (opciones.mantenerSeleccion !== true) {
        resetSeleccionAsignarTarea();
    }

    if (contenedor) {
        if ($.trim(contenedor.innerHTML) == "" || contenedor.querySelector(".asignar-tarea__vacio")) {
            contenedor.innerHTML = paginacargando;
        } else {
            contenedor.classList.add("asignar-tarea__contenedor--actualizando");
        }
    }

    obtener_datos_user();

    var datos = {
        "useru": userid,
        "passu": passuser,
        "navegador": navegador,
        "buscar": buscar,
        "tipo": tipo,
        "estado": estado,
        "rol_operativo": rolOperativo,
        "funt": "buscarUsuariosAsignarTarea"
    };

    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmTareaProgramada.php",
        type: "post",

        error: function(jqXHR, textstatus, errorThrowm) {
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
            if (contenedor) {
                contenedor.classList.remove("asignar-tarea__contenedor--actualizando");
                if ($.trim(contenedor.innerHTML) == "" || contenedor.innerHTML == paginacargando) {
                    contenedor.innerHTML = "<div class='asignar-tarea__vacio'><p>No pudimos cargar los funcionarios. Intenta nuevamente.</p></div>";
                }
            }
        },

        success: function(responseText) {
            var Respuesta = responseText;

            console.log(Respuesta);

            try {
                var datos = $.parseJSON(Respuesta);

                Respuesta = datos["1"];
                Respuesta = respuestaJqueryAjax(Respuesta);

                if (Respuesta == true) {
                    if (contenedor) {
                        contenedor.innerHTML = datos[2];
                        contenedor.classList.remove("asignar-tarea__contenedor--actualizando");
                        restaurarFuncionariosExpandidosAsignarTarea(expandidos);
                    }
                }

            } catch (error) {
                if (contenedor) {
                    contenedor.classList.remove("asignar-tarea__contenedor--actualizando");
                }
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
            document.getElementById("lblUsuarioTareaModal").innerHTML = tipoDestinoAsignarTarea == "ROL" ? ("rol " + rolDestinoAsignarTarea) : nombreUsuarioSeleccionadoTarea;
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
    if (tipoDestinoAsignarTarea == "USUARIO" && codUsuarioSeleccionadoTarea == "") {
        ver_vetana_informativa("FALTO SELECCIONAR UN USUARIO");
        return;
    }

    if (tipoDestinoAsignarTarea == "ROL" && rolDestinoAsignarTarea == "") {
        ver_vetana_informativa("FALTO INDICAR EL ROL OPERATIVO");
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
        "cod_usuario": codUsuarioSeleccionadoTarea,
        "tipo_destino": tipoDestinoAsignarTarea,
        "rol_operativo": rolDestinoAsignarTarea
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
    if (tipoDestinoAsignarTarea == "USUARIO" && codUsuarioSeleccionadoTarea == "") {
        ver_vetana_informativa("FALTO SELECCIONAR UN USUARIO");
        return;
    }

    if (tipoDestinoAsignarTarea == "ROL" && rolDestinoAsignarTarea == "") {
        ver_vetana_informativa("FALTO INDICAR EL ROL OPERATIVO");
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
        "fecha_tarea": fechaTarea,
        "tipo_destino": tipoDestinoAsignarTarea,
        "rol_operativo": rolDestinoAsignarTarea
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

                if (datos["1"] == "sinusuarios") {
                    ver_vetana_informativa(datos["mensaje"]);
                    return;
                }

                Respuesta = datos["1"];
                Respuesta = respuestaJqueryAjax(Respuesta);

                if (Respuesta == true) {
                    var mensaje = "TAREA ASIGNADA CORRECTAMENTE";

                    if (tipoDestinoAsignarTarea == "ROL" && datos["insertados"]) {
                        mensaje += ". Usuarios vinculados: " + datos["insertados"];
                    }

                    ver_vetana_informativa(mensaje);

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


function cargarTareasPendientesAdministrador(opciones) {
    opciones = opciones || {};

    var lista = document.getElementById("listaTareasAdministrador");
    var vacio = document.getElementById("tareasAdministradorVacio");

    if (!lista) {
        return;
    }

    agendaDiaTareasEstado.solicitud++;
    var solicitudActual = agendaDiaTareasEstado.solicitud;
    var tieneContenidoVisible = $.trim(lista.innerHTML) != "" && lista.innerHTML != paginacargando;
    var primeraCarga = agendaDiaTareasEstado.tieneRespuesta !== true && !tieneContenidoVisible;

    agendaDiaTareasEstado.cargando = true;
    setAgendaDiaActualizando(true);

    if (primeraCarga || opciones.forzarEstado === true && !tieneContenidoVisible) {
        lista.innerHTML = mostrarEstadoAgendaCargando();
        lista.style.display = "grid";
        if (vacio) {
            vacio.style.display = "none";
        }
        setResumenAgendaDia(0, 0, 0, 0, 0);
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
            if (solicitudActual != agendaDiaTareasEstado.solicitud) {
                return;
            }

            agendaDiaTareasEstado.cargando = false;
            setAgendaDiaActualizando(false);
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");

            if (agendaDiaTareasEstado.tieneRespuesta !== true || $.trim(agendaDiaTareasEstado.ultimoHtml) == "") {
                lista.innerHTML = "";
                lista.style.display = "none";
                if (vacio) {
                    vacio.innerHTML = mostrarEstadoAgendaError();
                    vacio.style.display = "block";
                }
                setResumenAgendaDia(0, 0, 0, 0, 0);
            }
        },

        success: function(responseText) {
            if (solicitudActual != agendaDiaTareasEstado.solicitud) {
                return;
            }

            var Respuesta = responseText;
            console.log("TAREAS PENDIENTES:", Respuesta);

            try {
                var datos = $.parseJSON(Respuesta);

                Respuesta = datos["1"];
                Respuesta = respuestaJqueryAjax(Respuesta);

                if (Respuesta == true) {
                    var html = datos[2] || "";
                    var totalPendientes = datos[3] || 0;
                    var totalTareas = datos[4] || 0;
                    var totalProceso = datos[5] || 0;
                    var totalCompletadas = datos[6] || 0;
                    var totalAtrasadas = datos[8] || 0;
                    var totalTareasNumero = parseInt(totalTareas, 10) || 0;

                    agendaDiaTareasEstado.tieneRespuesta = true;
                    agendaDiaTareasEstado.ultimoHtml = html;
                    agendaDiaTareasEstado.cargando = false;
                    setAgendaDiaActualizando(false);
                    setResumenAgendaDia(totalPendientes, totalProceso, totalAtrasadas, totalCompletadas, totalTareas);

                    if (totalTareasNumero > 0) {
                        lista.innerHTML = html;
                        lista.style.display = "grid";
                        if (vacio) {
                            vacio.style.display = "none";
                        }
                    } else {
                        lista.innerHTML = "";
                        lista.style.display = "none";
                        if (vacio) {
                            vacio.innerHTML = mostrarEstadoAgendaVacia();
                            vacio.style.display = "block";
                        }
                    }
                }

            } catch (error) {
                agendaDiaTareasEstado.cargando = false;
                setAgendaDiaActualizando(false);

                if (agendaDiaTareasEstado.tieneRespuesta !== true || $.trim(agendaDiaTareasEstado.ultimoHtml) == "") {
                    lista.innerHTML = "";
                    lista.style.display = "none";
                    if (vacio) {
                        vacio.innerHTML = mostrarEstadoAgendaError();
                        vacio.style.display = "block";
                    }
                    setResumenAgendaDia(0, 0, 0, 0, 0);
                }

                var titulo = "Error: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
            }
        }
    });
}


function cambiarEstadoTareaAsignada(check, codTareaAsignada, codUsuarioResponsable) {

    if (!check || !codTareaAsignada) {
        return;
    }

    var estadoNuevo = "";

    if (check.checked == true) {
        estadoNuevo = "Completada";
    } else {
        estadoNuevo = "Pendiente";
    }

    check.disabled = true;

    obtener_datos_user();

    var datos = {
        "useru": userid,
        "passu": passuser,
        "navegador": navegador,
        "funt": "cambiarEstadoTareaAsignada",
        "cod_tarea_asignada": codTareaAsignada,
        "estado_tarea": estadoNuevo
    };

    if (codUsuarioResponsable) {
        datos.cod_usuario_responsable = codUsuarioResponsable;
    }

    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmTareaProgramada.php",
        type: "post",

        error: function(jqXHR, textstatus, errorThrowm) {
            check.checked = !check.checked;
            check.disabled = false;
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

                    if (codUsuarioResponsable) {
                        buscarUsuariosAsignarTarea({
                            mantenerSeleccion: true,
                            expandidos: [String(codUsuarioResponsable)]
                        });
                    } else if (typeof cargarTareasPendientesAdministrador === "function") {
                        cargarTareasPendientesAdministrador({forzarEstado:false});
                    }

                } else {
                    check.checked = !check.checked;
                    check.disabled = false;
                }

            } catch (error) {

                check.checked = !check.checked;
                check.disabled = false;

                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR");

                var titulo = "Error: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
            }
        }
    });
}


var codTareaDiariaSeleccionada = "";
var nombreTareaDiariaSeleccionada = "";
var tareasDiariasSeleccionadas = [];

function confirmarUsuarioAsignadoTareaDiaria() {
    if (tipoDestinoAsignarTarea == "USUARIO" && codUsuarioSeleccionadoTarea == "") {
        ver_vetana_informativa("FALTO SELECCIONAR UN USUARIO");
        return;
    }

    if (tipoDestinoAsignarTarea == "ROL" && rolDestinoAsignarTarea == "") {
        ver_vetana_informativa("FALTO SELECCIONAR UN ROL");
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

        prepararDestinoTareaDiaria();
        limpiarSeleccionTareasDiarias();

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

function prepararDestinoTareaDiaria() {
    var radios = document.getElementsByName("radioDestinoTareaDiaria");
    var destinoInicial = tipoDestinoAsignarTarea == "ROL" ? "rol" : "usuario";

    for (var i = 0; i < radios.length; i++) {
        radios[i].checked = radios[i].value == destinoInicial;
    }

    if (document.getElementById("lblUsuarioTareaDiariaModal")) {
        document.getElementById("lblUsuarioTareaDiariaModal").innerHTML = tipoDestinoAsignarTarea == "ROL" ? ("Rol: " + rolDestinoAsignarTarea) : (nombreUsuarioSeleccionadoTarea || "usuario");
    }

    if (document.getElementById("inptLocalDestinoTareaDiaria") && document.getElementById("inptBuscarTipoUsuarioAsignarTarea")) {
        document.getElementById("inptLocalDestinoTareaDiaria").innerHTML = document.getElementById("inptBuscarTipoUsuarioAsignarTarea").innerHTML;
    }

    if (document.getElementById("inptRolOperativoTareaDiaria") && rolDestinoAsignarTarea != "") {
        asegurarOpcionRolTareaDiaria(rolDestinoAsignarTarea);
        document.getElementById("inptRolOperativoTareaDiaria").value = rolDestinoAsignarTarea;
    }

    cambiarDestinoTareaDiaria();
}

function asegurarOpcionRolTareaDiaria(rolOperativo) {
    var selectRol = document.getElementById("inptRolOperativoTareaDiaria");

    if (!selectRol || rolOperativo == "") {
        return;
    }

    for (var i = 0; i < selectRol.options.length; i++) {
        if (selectRol.options[i].value == rolOperativo) {
            return;
        }
    }

    var option = document.createElement("option");
    option.value = rolOperativo;
    option.text = rolOperativo;
    selectRol.appendChild(option);
}

function obtenerDestinoTareaDiaria() {
    var radios = document.getElementsByName("radioDestinoTareaDiaria");

    for (var i = 0; i < radios.length; i++) {
        if (radios[i].checked) {
            return radios[i].value;
        }
    }

    return "usuario";
}

function cambiarDestinoTareaDiaria() {
    var destino = obtenerDestinoTareaDiaria();
    var selectRol = document.getElementById("inptRolOperativoTareaDiaria");
    var selectLocal = document.getElementById("inptLocalDestinoTareaDiaria");
    var ayuda = document.getElementById("txtDestinoTareaDiariaAyuda");

    if (selectRol) {
        selectRol.disabled = destino != "rol";
    }

    if (selectLocal) {
        selectLocal.disabled = destino != "local";
    }

    if (!ayuda) {
        return;
    }

    if (destino == "usuario") {
        ayuda.innerHTML = "La asignacion se guardara para el usuario seleccionado y usara la configuracion diaria actual.";
    } else if (destino == "rol") {
        ayuda.innerHTML = "La configuracion diaria se guardara para el rol operativo seleccionado y generara tareas para sus usuarios activos.";
    } else {
        ayuda.innerHTML = "Local / sucursal queda preparado para aplicar tareas por sede. El guardado masivo por local se habilita en la siguiente fase.";
    }
}

function limpiarCamposCrearTareaDiaria() {
    if (document.getElementById("inptNombreCrearTareaDiaria")) {
        document.getElementById("inptNombreCrearTareaDiaria").value = "";
    }

    if (document.getElementById("inptHoraCrearTareaDiaria")) {
        document.getElementById("inptHoraCrearTareaDiaria").value = "";
    }

    if (document.getElementById("inptTipoCrearTareaDiaria")) {
        document.getElementById("inptTipoCrearTareaDiaria").value = "DIARIO";
    }
}

function verCerrarModalCrearTareaDiaria(mostrar) {
    var modal = document.getElementById("modalCrearTareaDiaria");

    if (!modal) {
        return;
    }

    if (mostrar) {
        limpiarCamposCrearTareaDiaria();
        modal.style.display = "";

        setTimeout(function() {
            if (document.getElementById("inptNombreCrearTareaDiaria")) {
                document.getElementById("inptNombreCrearTareaDiaria").focus();
            }
        }, 80);
    } else {
        modal.style.display = "none";
    }
}

function guardarNuevaTareaDiaria() {
    var nombre = "";
    var hora = "";

    if (document.getElementById("inptNombreCrearTareaDiaria")) {
        nombre = document.getElementById("inptNombreCrearTareaDiaria").value;
    }

    if (document.getElementById("inptHoraCrearTareaDiaria")) {
        hora = document.getElementById("inptHoraCrearTareaDiaria").value;
    }

    if (nombre == "") {
        ver_vetana_informativa("FALTO INGRESAR EL NOMBRE DE LA TAREA");
        return;
    }

    if (hora == "") {
        ver_vetana_informativa("FALTO INGRESAR LA HORA");
        return;
    }

    verCerrarEfectoCargando("1");
    obtener_datos_user();

    var datos = {
        "useru": userid,
        "passu": passuser,
        "navegador": navegador,
        "funt": "nuevo",
        "id": "",
        "nombre": nombre,
        "hora": hora,
        "tipo": "DIARIO",
        "estado": "pendiente",
        "cod_usuarioFK": userid
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

            console.log("CREAR TAREA DIARIA:", Respuesta);

            try {
                var datos = $.parseJSON(Respuesta);

                Respuesta = datos["1"];
                Respuesta = respuestaJqueryAjax(Respuesta);

                if (Respuesta == true) {
                    ver_vetana_informativa("TAREA DIARIA CREADA CORRECTAMENTE");
                    verCerrarModalCrearTareaDiaria(false);

                    if (document.getElementById("inptBuscarTipoTareaDiaria")) {
                        document.getElementById("inptBuscarTipoTareaDiaria").value = "DIARIO";
                    }

                    if (document.getElementById("modalAsignarTareaDiaria") && document.getElementById("modalAsignarTareaDiaria").style.display == "") {
                        buscarTareasParaAsignarDiariaUsuario();
                    }

                    if (typeof buscarabmTareaProgramada == "function") {
                        buscarabmTareaProgramada();
                    }
                } else {
                    ver_vetana_informativa("NO SE PUDO CREAR LA TAREA DIARIA");
                }

            } catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR");

                var titulo = "Error: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
            }
        }
    });
}

function obtenerTareaDiariaDesdeCard(card) {
    if (!card) {
        return null;
    }

    return {
        id: card.getAttribute("data-id") || "",
        nombre: card.getAttribute("data-nombre") || "",
        hora: card.getAttribute("data-hora") || "",
        tipo: card.getAttribute("data-tipo") || ""
    };
}

function existeTareaDiariaSeleccionada(idTarea) {
    for (var i = 0; i < tareasDiariasSeleccionadas.length; i++) {
        if (tareasDiariasSeleccionadas[i].id == idTarea) {
            return i;
        }
    }

    return -1;
}

function setCardTareaDiariaSeleccionada(card, seleccionar) {
    if (!card || card.classList.contains("tarea-diaria-modal__card--asignada")) {
        return;
    }

    var tarea = obtenerTareaDiariaDesdeCard(card);

    if (!tarea || tarea.id == "") {
        return;
    }

    var posicion = existeTareaDiariaSeleccionada(tarea.id);

    if (seleccionar && posicion == -1) {
        tareasDiariasSeleccionadas.push(tarea);
    }

    if (!seleccionar && posicion >= 0) {
        tareasDiariasSeleccionadas.splice(posicion, 1);
    }

    card.classList.toggle("tarea-diaria-modal__card--activo", seleccionar);

    var check = card.querySelector("input[type='checkbox']");

    if (check) {
        check.checked = seleccionar;
    }
}

function seleccionarTareaDiariaParaAsignar(elemento) {
    var card = elemento;

    if (elemento && elemento.closest && !elemento.classList.contains("tarea-diaria-modal__card")) {
        card = elemento.closest(".tarea-diaria-modal__card");
    }

    if (!card || card.classList.contains("tarea-diaria-modal__card--asignada")) {
        return;
    }

    var tarea = obtenerTareaDiariaDesdeCard(card);
    var yaSeleccionada = existeTareaDiariaSeleccionada(tarea.id) >= 0;

    setCardTareaDiariaSeleccionada(card, !yaSeleccionada);
    actualizarResumenSeleccionTareaDiaria();
}

function seleccionarTodasTareasDiariasDisponibles() {
    var cards = document.querySelectorAll("#contenedorTareasDiariasParaAsignar .tarea-diaria-modal__card:not(.tarea-diaria-modal__card--asignada)");

    for (var i = 0; i < cards.length; i++) {
        setCardTareaDiariaSeleccionada(cards[i], true);
    }

    actualizarResumenSeleccionTareaDiaria();
}

function limpiarSeleccionTareasDiarias() {
    tareasDiariasSeleccionadas = [];
    codTareaDiariaSeleccionada = "";
    nombreTareaDiariaSeleccionada = "";

    if (document.getElementById("inptCodTareaDiariaSeleccionada")) {
        document.getElementById("inptCodTareaDiariaSeleccionada").value = "";
    }

    var cards = document.getElementsByClassName("tarea-diaria-modal__card");

    for (var i = 0; i < cards.length; i++) {
        cards[i].classList.remove("tarea-diaria-modal__card--activo");

        var check = cards[i].querySelector("input[type='checkbox']");

        if (check) {
            check.checked = false;
        }
    }

    actualizarResumenSeleccionTareaDiaria();
}

function actualizarResumenSeleccionTareaDiaria() {
    var cantidad = tareasDiariasSeleccionadas.length;
    var nombres = [];

    for (var i = 0; i < tareasDiariasSeleccionadas.length; i++) {
        nombres.push((tareasDiariasSeleccionadas[i].hora ? tareasDiariasSeleccionadas[i].hora + " " : "") + tareasDiariasSeleccionadas[i].nombre);
    }

    codTareaDiariaSeleccionada = cantidad > 0 ? tareasDiariasSeleccionadas[0].id : "";
    nombreTareaDiariaSeleccionada = nombres.join(", ");

    if (document.getElementById("inptCodTareaDiariaSeleccionada")) {
        document.getElementById("inptCodTareaDiariaSeleccionada").value = codTareaDiariaSeleccionada;
    }

    if (document.getElementById("lblCantidadTareasDiariasSeleccionadas")) {
        document.getElementById("lblCantidadTareasDiariasSeleccionadas").innerHTML = cantidad + (cantidad == 1 ? " tarea seleccionada" : " tareas seleccionadas");
    }

    if (document.getElementById("boxTareaDiariaSeleccionada")) {
        document.getElementById("boxTareaDiariaSeleccionada").style.display = cantidad > 0 ? "" : "none";
    }

    if (document.getElementById("lblTareaDiariaSeleccionada")) {
        document.getElementById("lblTareaDiariaSeleccionada").innerHTML = cantidad > 0 ? nombres.join("<br>") : "";
    }

    if (document.getElementById("lblResumenTareaDiaria")) {
        var destino = obtenerDestinoTareaDiaria();
        var destinoTexto = nombreUsuarioSeleccionadoTarea || "usuario";

        if (destino == "rol" && document.getElementById("inptRolOperativoTareaDiaria")) {
            destinoTexto = "Rol: " + document.getElementById("inptRolOperativoTareaDiaria").value;
        }

        document.getElementById("lblResumenTareaDiaria").innerHTML = cantidad > 0 ? "Destinatario: " + destinoTexto + " | Frecuencia: diaria" : "";
    }
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
    var destino = obtenerDestinoTareaDiaria();
    var rolOperativo = "";

    if (document.getElementById("inptRolOperativoTareaDiaria")) {
        rolOperativo = document.getElementById("inptRolOperativoTareaDiaria").value;
    }

    if (destino == "usuario" && codUsuarioSeleccionadoTarea == "") {
        ver_vetana_informativa("FALTO SELECCIONAR UN USUARIO");
        return;
    }

    if (destino == "rol" && rolOperativo == "") {
        ver_vetana_informativa("FALTO SELECCIONAR EL ROL OPERATIVO");
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
        "cod_usuario": codUsuarioSeleccionadoTarea,
        "tipo_destino": destino.toUpperCase(),
        "rol_operativoFK": rolOperativo
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
                    limpiarSeleccionTareasDiarias();
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
    var destino = obtenerDestinoTareaDiaria();
    var rolOperativo = "";

    if (document.getElementById("inptRolOperativoTareaDiaria")) {
        rolOperativo = document.getElementById("inptRolOperativoTareaDiaria").value;
    }

    if (destino == "usuario" && codUsuarioSeleccionadoTarea == "") {
        ver_vetana_informativa("FALTO SELECCIONAR UN USUARIO");
        return;
    }

    if (destino == "rol" && rolOperativo == "") {
        ver_vetana_informativa("FALTO SELECCIONAR EL ROL OPERATIVO");
        return;
    }

    if (destino == "local") {
        ver_vetana_informativa("La asignacion por local queda preparada para la siguiente fase. Por ahora use usuario especifico o rol operativo.");
        return;
    }

    if (tareasDiariasSeleccionadas.length == 0 && codTareaDiariaSeleccionada != "") {
        tareasDiariasSeleccionadas.push({
            id: codTareaDiariaSeleccionada,
            nombre: nombreTareaDiariaSeleccionada,
            hora: "",
            tipo: ""
        });
    }

    if (tareasDiariasSeleccionadas.length == 0) {
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

    var datosBase = {
        "useru": userid,
        "passu": passuser,
        "navegador": navegador,
        "funt": "guardarTareaDiariaUsuario",
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
        "observacion_admin": observacion,
        "tipo_destino": destino.toUpperCase(),
        "rol_operativoFK": rolOperativo
    };

    guardarTareasDiariasSeleccionadas(0, datosBase, {
        guardadas: 0,
        duplicadas: 0,
        errores: 0
    });
}

function guardarTareasDiariasSeleccionadas(indice, datosBase, resultado) {
    if (indice >= tareasDiariasSeleccionadas.length) {
        verCerrarEfectoCargando("");

        var mensaje = "Asignacion finalizada. Guardadas: " + resultado.guardadas + ".";

        if (resultado.duplicadas > 0) {
            mensaje += " Ya configuradas: " + resultado.duplicadas + ".";
        }

        if (resultado.errores > 0) {
            mensaje += " Errores: " + resultado.errores + ".";
        }

        ver_vetana_informativa(mensaje);
        buscarTareasParaAsignarDiariaUsuario();

        if (typeof cargarTareasPendientesAdministrador === "function") {
            cargarTareasPendientesAdministrador();
        }

        return;
    }

    var tarea = tareasDiariasSeleccionadas[indice];
    var datos = {};

    for (var key in datosBase) {
        if (Object.prototype.hasOwnProperty.call(datosBase, key)) {
            datos[key] = datosBase[key];
        }
    }

    datos.cod_tareaFK = tarea.id;

    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmTareaProgramada.php",
        type: "post",

        error: function(jqXHR, textstatus, errorThrowm) {
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
            resultado.errores++;
            guardarTareasDiariasSeleccionadas(indice + 1, datosBase, resultado);
        },

        success: function(responseText) {
            var Respuesta = responseText;

            console.log("GUARDAR TAREA DIARIA:", Respuesta);

            try {
                var datos = $.parseJSON(Respuesta);

                if (datos["1"] == "duplicado") {
                    resultado.duplicadas++;
                    guardarTareasDiariasSeleccionadas(indice + 1, datosBase, resultado);
                    return;
                }

                Respuesta = datos["1"];
                Respuesta = respuestaJqueryAjax(Respuesta);

                if (Respuesta == true) {
                    resultado.guardadas++;
                } else {
                    resultado.errores++;
                }

                guardarTareasDiariasSeleccionadas(indice + 1, datosBase, resultado);

            } catch (error) {
                var titulo = "Error: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
                resultado.errores++;
                guardarTareasDiariasSeleccionadas(indice + 1, datosBase, resultado);
            }
        }
    });
}

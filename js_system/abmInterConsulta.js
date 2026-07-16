var controldebusquedadInformeInterConsulta= true;
var totalregistroinformeInterConsulta= 0;
var registrocargadoInterConsulta= 0;
var paginaOffsetInterConsulta= 0;
var registroInterConsultaAbierta= 0;
var cod_interConsulta= "";
var temporizadorBusquedaGlobalInterConsulta= null;
var busquedaInterConsultaCancelada= false;
var limiteMaximoListadoInterConsulta= 30;
var filtrosUltimaBusquedaInterConsulta= null;
var solicitudListadoInterConsultaActiva= null;
var solicitudEnriquecimientoListadoInterConsultaActiva= null;
var secuenciaListadoInterConsulta= 0;
var solicitudDetalleInterConsultaActiva= null;
var solicitudInterConsultasAsociadasActiva= null;
var secuenciaInterConsultasAsociadas= 0;
var plantillasSeguimientoInterConsulta= [];
var plantillasAdministracionSeguimientoInterConsulta= [];
var responsablesSeguimientoInterConsulta= [];
var solicitudContextoSeguimientoInterConsultaActiva= null;
var solicitudContextoMensajeInterConsultaActiva= null;
var secuenciaContextoMensajeInterConsulta= 0;
var solicitudAlertasSeguimientoInterConsultaActiva= null;
var secuenciaAlertasSeguimientoInterConsulta= 0;
var firmaAlertasSeguimientoInterConsulta= "";
var temporizadorAlertasSeguimientoInterConsulta= null;
var intervaloAlertasSeguimientoInterConsultaMs= 120000;
var manejadorTimelineSeguimientoInterConsultaInicializado= false;
var manejadorDialogoSeguimientoInterConsultaInicializado= false;
var elementoFocoAnteriorSeguimientoInterConsulta= null;
var idSeguimientoAlertaPendienteInterConsulta= 0;
var categoriaActivaInterConsulta= "pagos_egresos";
var categoriaOriginalAbmInterConsulta= "";
var tipoOriginalAbmInterConsulta= "";
var categoriasHilosInterConsulta= {
    pagos_egresos: {
        nombre: "Pagos y Egresos",
        subtipos: [
            {valor: "pagos", texto: "Pagos"},
            {valor: "compras", texto: "Compras"},
            {valor: "egresos", texto: "Egresos"}
        ],
        tiposNormalizados: ["pagos", "pago", "compras", "compra", "egresos", "egreso"]
    },
    judiciales: {
        nombre: "Judiciales",
        subtipos: [
            {valor: "judicial", texto: "Judicial"}
        ],
        tiposNormalizados: ["judicial", "judiciales"]
    },
    administrativo_clinico: {
        nombre: "Administrativo y Clinico",
        subtipos: [
            {valor: "administrativo", texto: "Administrativo"},
            {valor: "clinico", texto: "Clinico"},
            {valor: "interno", texto: "Interno"}
        ],
        tiposNormalizados: ["administrativo", "clinico", "interno"]
    }
};

try {
    var categoriaGuardadaInterConsulta= window.sessionStorage ? sessionStorage.getItem("categoriaActivaInterConsulta") : "";
    if (categoriaGuardadaInterConsulta && categoriasHilosInterConsulta[categoriaGuardadaInterConsulta]) {
        categoriaActivaInterConsulta= categoriaGuardadaInterConsulta;
    }
} catch (error) {}

function normalizarTextoHiloInterConsulta(valor) {
    var texto= (valor || "")
        .toString()
        .trim()
        .toLowerCase();
    if (texto.normalize) {
        texto= texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    }
    return texto
        .replace(/[^a-z0-9]+/g, "_")
        .replace(/^_+|_+$/g, "");
}

function obtenerCategoriaPrincipalHilo(tipoOriginal) {
    var tipo= normalizarTextoHiloInterConsulta(tipoOriginal);
    var categorias= Object.keys(categoriasHilosInterConsulta);
    for (var i= 0; i < categorias.length; i++) {
        var categoria= categorias[i];
        if (categoriasHilosInterConsulta[categoria].tiposNormalizados.indexOf(tipo) >= 0) {
            return categoria;
        }
    }
    return "";
}

function obtenerCategoriaActivaInterConsulta() {
    if (!categoriasHilosInterConsulta[categoriaActivaInterConsulta]) {
        categoriaActivaInterConsulta= "pagos_egresos";
    }
    return categoriaActivaInterConsulta;
}

function actualizarTabsCategoriaHilosInterConsulta(conteos) {
    var categoriaActiva= obtenerCategoriaActivaInterConsulta();
    var contenedorListado= document.getElementById("divListadoInterConsulta");
    if (contenedorListado) {
        contenedorListado.setAttribute("data-hilos-category", categoriaActiva);
    }

    document.querySelectorAll(".hilos-category-tab").forEach(function(tab) {
        var categoria= tab.getAttribute("data-hilos-category");
        var activo= categoria == categoriaActiva;
        tab.classList.toggle("hilos-category-tab--active", activo);
        tab.setAttribute("aria-selected", activo ? "true" : "false");
        tab.setAttribute("tabindex", activo ? "0" : "-1");
    });

    if (conteos) {
        Object.keys(categoriasHilosInterConsulta).forEach(function(categoria) {
            var contador= document.getElementById("countCategoriaHilos_" + categoria);
            if (contador) {
                contador.textContent= conteos[categoria] !== undefined ? conteos[categoria] : "0";
            }
        });
    }
}

function actualizarOpcionesSelectHilosInterConsulta(select, opciones, valorActual, incluirTodos) {
    if (!select) {
        return;
    }

    select.innerHTML= "";
    if (incluirTodos) {
        var opcionTodos= document.createElement("option");
        opcionTodos.value= "";
        opcionTodos.textContent= "Todos";
        select.appendChild(opcionTodos);
    }

    opciones.forEach(function(opcion) {
        var elemento= document.createElement("option");
        elemento.value= opcion.valor;
        elemento.textContent= opcion.texto;
        select.appendChild(elemento);
    });

    select.value= valorActual || "";
    if (select.value != (valorActual || "")) {
        select.value= "";
    }
}

function actualizarOpcionesSubtipoInterConsulta() {
    var categoria= obtenerCategoriaActivaInterConsulta();
    var datosCategoria= categoriasHilosInterConsulta[categoria];
    var selectRapido= document.getElementById("inptFiltroRapidoTipoInterConsulta");
    var selectAvanzado= document.getElementById("inptBuscarInterConsulta4");
    var campoRapido= document.getElementById("campoFiltroRapidoSubtipoInterConsulta");
    var campoAvanzado= document.getElementById("campoFiltroAvanzadoSubtipoInterConsulta");
    var valorRapido= selectRapido ? selectRapido.value : "";
    var valorAvanzado= selectAvanzado ? selectAvanzado.value : "";
    var ocultarSubtipo= categoria == "judiciales";

    if (ocultarSubtipo) {
        valorRapido= "";
        valorAvanzado= "";
    }

    actualizarOpcionesSelectHilosInterConsulta(selectRapido, datosCategoria.subtipos, valorRapido, true);
    actualizarOpcionesSelectHilosInterConsulta(selectAvanzado, datosCategoria.subtipos, valorAvanzado || valorRapido, true);

    if (campoRapido) {
        campoRapido.style.display= ocultarSubtipo ? "none" : "";
    }
    if (campoAvanzado) {
        campoAvanzado.style.display= ocultarSubtipo ? "none" : "";
    }
}

function actualizarOpcionesSubtipoAbmInterConsulta(categoria, valorActual) {
    var selectTipo= document.getElementById("inptTipoAbmInterConsulta");
    var campoSubtipo= document.getElementById("campoSubtipoAbmInterConsulta");
    if (!selectTipo) {
        return;
    }

    categoria= categoriasHilosInterConsulta[categoria] ? categoria : obtenerCategoriaActivaInterConsulta();
    var opciones= categoriasHilosInterConsulta[categoria].subtipos.slice();
    var tipoNormalizado= normalizarTextoHiloInterConsulta(valorActual);
    var tipoReconocido= obtenerCategoriaPrincipalHilo(valorActual) != "";

    selectTipo.innerHTML= "";
    if (valorActual !== undefined && valorActual !== null && !tipoReconocido) {
        var opcionActual= document.createElement("option");
        opcionActual.value= valorActual;
        opcionActual.textContent= valorActual ? ("Tipo actual: " + valorActual) : "Sin tipo actual";
        selectTipo.appendChild(opcionActual);
    }

    opciones.forEach(function(opcion) {
        var elemento= document.createElement("option");
        elemento.value= opcion.valor;
        elemento.textContent= opcion.texto;
        selectTipo.appendChild(elemento);
    });

    if (valorActual !== undefined && valorActual !== null && (!tipoReconocido || tipoNormalizado != "")) {
        selectTipo.value= valorActual;
    }

    if (!selectTipo.value && opciones.length > 0 && tipoReconocido) {
        selectTipo.value= opciones[0].valor;
    }

    if (campoSubtipo) {
        campoSubtipo.style.display= "";
    }
}

function prepararCategoriaAbmDesdeTipo(tipoOriginal, registrarOriginal= true) {
    var categoria= obtenerCategoriaPrincipalHilo(tipoOriginal) || obtenerCategoriaActivaInterConsulta();
    var selectCategoria= document.getElementById("inptCategoriaAbmInterConsulta");
    if (selectCategoria) {
        selectCategoria.value= categoria;
    }
    actualizarOpcionesSubtipoAbmInterConsulta(categoria, tipoOriginal);

    if (registrarOriginal) {
        categoriaOriginalAbmInterConsulta= obtenerCategoriaPrincipalHilo(tipoOriginal) || categoria;
        tipoOriginalAbmInterConsulta= tipoOriginal || "";
    }
}

function actualizarCategoriaAbmInterConsulta() {
    var selectCategoria= document.getElementById("inptCategoriaAbmInterConsulta");
    var categoria= selectCategoria ? selectCategoria.value : obtenerCategoriaActivaInterConsulta();
    actualizarOpcionesSubtipoAbmInterConsulta(categoria);
}

function cerrarDetallePorCambioCategoriaHilo() {
    if (!cod_interConsulta) {
        return;
    }
    cod_interConsulta= "";
    verCerrarVentanaDetalleInterConsulta(false);
    limpiarCamposDetallesInterConsulta();
}

function seleccionarCategoriaHilosInterConsulta(categoria, ejecutarBusqueda= true) {
    if (!categoriasHilosInterConsulta[categoria]) {
        return;
    }

    var cambioCategoria= categoriaActivaInterConsulta != categoria;
    categoriaActivaInterConsulta= categoria;
    try {
        if (window.sessionStorage) {
            sessionStorage.setItem("categoriaActivaInterConsulta", categoria);
        }
    } catch (error) {}

    asignarValorCampoInterConsulta("inptFiltroRapidoTipoInterConsulta", "");
    asignarValorCampoInterConsulta("inptBuscarInterConsulta4", "");
    actualizarOpcionesSubtipoInterConsulta();
    actualizarTabsCategoriaHilosInterConsulta();

    if (cambioCategoria) {
        cerrarDetallePorCambioCategoriaHilo();
    }

    if (ejecutarBusqueda) {
        buscarPacientesConInterConsultas();
    }
}

function manejarTeclaCategoriaHilosInterConsulta(event, categoria) {
    if (event.key == "Enter" || event.key == " ") {
        event.preventDefault();
        seleccionarCategoriaHilosInterConsulta(categoria);
    }
}

function obtenerTotalPaginasHilosInterConsulta() {
    var total= Number(totalregistroinformeInterConsulta) || 0;
    return total > 0 ? Math.ceil(total / limiteMaximoListadoInterConsulta) : 0;
}

function obtenerPaginaActualHilosInterConsulta() {
    var totalPaginas= obtenerTotalPaginasHilosInterConsulta();
    if (totalPaginas == 0) {
        return 0;
    }
    return Math.min(Math.floor((Number(paginaOffsetInterConsulta) || 0) / limiteMaximoListadoInterConsulta) + 1, totalPaginas);
}

function puedeCargarMasHilosInterConsulta() {
    var total= Number(totalregistroinformeInterConsulta) || 0;
    var offset= Number(paginaOffsetInterConsulta) || 0;
    var registrosPagina= Number(registrocargadoInterConsulta) || 0;
    return total > 0 && (offset + registrosPagina) < total;
}

function puedeRetrocederHilosInterConsulta() {
    return (Number(paginaOffsetInterConsulta) || 0) > 0;
}

function limitePaginaHilosInterConsulta(offset) {
    offset= Math.max(0, Number(offset) || 0);
    return limiteMaximoListadoInterConsulta + (offset > 0 ? " OFFSET " + offset : "");
}

function guardarFiltrosBusquedaInterConsulta(cod_interC, asunto, nombre_responsable, nombre_cliente, estado, tipo, cod_localFK, cod_usuarioFK, ocultar_inactivos, usuario_vinculado, busqueda_global, fecha_desde, fecha_hasta, categoria_principal) {
    filtrosUltimaBusquedaInterConsulta= {
        cod_interC: cod_interC,
        asunto: asunto,
        nombre_responsable: nombre_responsable,
        nombre_cliente: nombre_cliente,
        estado: estado,
        tipo: tipo,
        cod_localFK: cod_localFK,
        cod_usuarioFK: cod_usuarioFK,
        ocultar_inactivos: ocultar_inactivos,
        usuario_vinculado: usuario_vinculado,
        busqueda_global: busqueda_global,
        fecha_desde: fecha_desde,
        fecha_hasta: fecha_hasta,
        categoria_principal: categoria_principal || obtenerCategoriaActivaInterConsulta()
    };
}

function actualizarBotonCargarMasHilosInterConsulta() {
    var botonSiguiente= document.getElementById("btnCargarMasHilosInterConsulta");
    var botonAnterior= document.getElementById("btnPaginaAnteriorHilosInterConsulta");
    var resumen= document.getElementById("txtCargaHilosInterConsulta");
    var total= Number(totalregistroinformeInterConsulta) || 0;
    var offset= Number(paginaOffsetInterConsulta) || 0;
    var registrosPagina= Number(registrocargadoInterConsulta) || 0;
    var hayAnterior= puedeRetrocederHilosInterConsulta() && !busquedaInterConsultaCancelada;
    var haySiguiente= puedeCargarMasHilosInterConsulta() && !busquedaInterConsultaCancelada;

    if (botonAnterior) {
        botonAnterior.style.display= total > limiteMaximoListadoInterConsulta ? "" : "none";
        botonAnterior.disabled= !hayAnterior;
        botonAnterior.value= "\u2190 Anterior";
    }

    if (botonSiguiente) {
        botonSiguiente.style.display= total > limiteMaximoListadoInterConsulta ? "" : "none";
        botonSiguiente.disabled= !haySiguiente;
        botonSiguiente.value= "Siguiente \u2192";
    }

    if (resumen) {
        if (total > 0 && registrosPagina > 0) {
            var inicio= offset + 1;
            var fin= Math.min(offset + registrosPagina, total);
            resumen.textContent= "Pagina " + obtenerPaginaActualHilosInterConsulta() + " de " + obtenerTotalPaginasHilosInterConsulta() + " - Mostrando " + inicio + "-" + fin + " de " + total;
        } else {
            resumen.textContent= "";
        }
    }
}

function valorCampoInterConsulta(id) {
    const elemento= document.getElementById(id);
    return elemento ? elemento.value : "";
}

function obtenerLocalUsuarioInterConsulta() {
    if (typeof cod_localFKUSer == "undefined" || cod_localFKUSer === null) {
        return "";
    }
    var localUsuario= String(cod_localFKUSer).trim();
    return localUsuario != "" && localUsuario != "0" ? localUsuario : "";
}

function asignarValorCampoInterConsulta(id, valor) {
    const elemento= document.getElementById(id);
    if (elemento) {
        elemento.value= valor || "";
    }
}

function dosDigitosInterConsulta(valor) {
    valor= String(valor);
    return valor.length < 2 ? "0" + valor : valor;
}

function obtenerFechaActualIsoInterConsulta() {
    var hoy= new Date();
    hoy.setHours(0, 0, 0, 0);
    var mes= dosDigitosInterConsulta(hoy.getMonth() + 1);
    var dia= dosDigitosInterConsulta(hoy.getDate());
    return hoy.getFullYear() + "-" + mes + "-" + dia;
}

function esFechaIsoInterConsulta(valor) {
    return /^\d{4}-\d{2}-\d{2}$/.test(String(valor || ""));
}

function normalizarFechaIsoInterConsulta(valor) {
    valor= String(valor || "").trim();
    if (!esFechaIsoInterConsulta(valor)) {
        return obtenerFechaActualIsoInterConsulta();
    }
    var fecha= new Date(valor + "T00:00:00");
    if (isNaN(fecha.getTime())) {
        return obtenerFechaActualIsoInterConsulta();
    }
    return valor;
}

function sumarDiasFechaIsoInterConsulta(valor, dias) {
    var fecha= new Date(normalizarFechaIsoInterConsulta(valor) + "T00:00:00");
    fecha.setDate(fecha.getDate() + (Number(dias) || 0));
    var mes= dosDigitosInterConsulta(fecha.getMonth() + 1);
    var dia= dosDigitosInterConsulta(fecha.getDate());
    return fecha.getFullYear() + "-" + mes + "-" + dia;
}

function formatearFechaCortaInterConsulta(valor) {
    valor= normalizarFechaIsoInterConsulta(valor);
    var partes= valor.split("-");
    return partes[2] + "/" + partes[1] + "/" + partes[0];
}

function obtenerDiaTextoInterConsulta(valor) {
    valor= normalizarFechaIsoInterConsulta(valor);
    if (valor == obtenerFechaActualIsoInterConsulta()) {
        return "Hoy";
    }
    var fecha= new Date(valor + "T00:00:00");
    var dias= ["Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado"];
    return dias[fecha.getDay()] || "Fecha";
}

function actualizarVistaFechaRapidaInterConsulta() {
    var campoRapido= document.getElementById("inptFiltroRapidoFechaInterConsulta");
    var textoDia= document.getElementById("textoDiaRapidoInterConsulta");
    var textoFecha= document.getElementById("textoFechaRapidaInterConsulta");
    if (!campoRapido || !textoDia || !textoFecha) {
        return;
    }

    var fechaDesde= valorCampoInterConsulta("inptBuscarInterConsultaFechaDesde");
    var fechaHasta= valorCampoInterConsulta("inptBuscarInterConsultaFechaHasta");
    if ((fechaDesde != "" || fechaHasta != "") && fechaDesde != fechaHasta) {
        textoDia.textContent= "Rango";
        textoFecha.textContent= (fechaDesde != "" ? formatearFechaCortaInterConsulta(fechaDesde) : "--/--/----")
            + " - "
            + (fechaHasta != "" ? formatearFechaCortaInterConsulta(fechaHasta) : "--/--/----");
        return;
    }

    var fecha= campoRapido.value || fechaDesde || fechaHasta || obtenerFechaActualIsoInterConsulta();
    fecha= normalizarFechaIsoInterConsulta(fecha);
    campoRapido.value= fecha;
    textoDia.textContent= obtenerDiaTextoInterConsulta(fecha);
    textoFecha.textContent= formatearFechaCortaInterConsulta(fecha);
}

function inicializarFechaRapidaInterConsulta() {
    var campoRapido= document.getElementById("inptFiltroRapidoFechaInterConsulta");
    if (!campoRapido) {
        return;
    }

    var fechaDesde= valorCampoInterConsulta("inptBuscarInterConsultaFechaDesde");
    var fechaHasta= valorCampoInterConsulta("inptBuscarInterConsultaFechaHasta");
    if (fechaDesde == "" && fechaHasta == "" && campoRapido.value == "") {
        aplicarFechaRapidaInterConsulta(obtenerFechaActualIsoInterConsulta(), false);
        return;
    }

    if (fechaDesde != "" && fechaDesde == fechaHasta) {
        campoRapido.value= normalizarFechaIsoInterConsulta(fechaDesde);
    }
    actualizarVistaFechaRapidaInterConsulta();
}

function aplicarFechaRapidaInterConsulta(fecha, ejecutarBusqueda) {
    fecha= normalizarFechaIsoInterConsulta(fecha);
    asignarValorCampoInterConsulta("inptFiltroRapidoFechaInterConsulta", fecha);
    asignarValorCampoInterConsulta("inptBuscarInterConsultaFechaDesde", fecha);
    asignarValorCampoInterConsulta("inptBuscarInterConsultaFechaHasta", fecha);
    actualizarVistaFechaRapidaInterConsulta();
    actualizarResumenControlesInterConsulta();
    if (ejecutarBusqueda) {
        aplicarFiltrosInterConsultaDesdeBarra();
    }
}

function moverFechaRapidaInterConsulta(dias) {
    var campoRapido= document.getElementById("inptFiltroRapidoFechaInterConsulta");
    var fechaDesde= valorCampoInterConsulta("inptBuscarInterConsultaFechaDesde");
    var fechaHasta= valorCampoInterConsulta("inptBuscarInterConsultaFechaHasta");
    var base= campoRapido && campoRapido.value ? campoRapido.value : "";
    if (base == "" && fechaDesde != "" && fechaDesde == fechaHasta) {
        base= fechaDesde;
    }
    if (base == "" && fechaDesde != "") {
        base= fechaDesde;
    }
    if (base == "" && fechaHasta != "") {
        base= fechaHasta;
    }
    if (base == "") {
        base= obtenerFechaActualIsoInterConsulta();
    }
    aplicarFechaRapidaInterConsulta(sumarDiasFechaIsoInterConsulta(base, dias), true);
}

function abrirCalendarioRapidoInterConsulta() {
    var campoRapido= document.getElementById("inptFiltroRapidoFechaInterConsulta");
    if (!campoRapido) {
        return;
    }
    if (typeof campoRapido.showPicker == "function") {
        campoRapido.showPicker();
    } else {
        campoRapido.focus();
        campoRapido.click();
    }
}

function asignarValorSelectInterConsulta(id, valor) {
    const elemento= document.getElementById(id);
    if (!elemento) {
        return false;
    }
    elemento.value= valor || "";
    return elemento.value == (valor || "");
}

var localUsuarioInicializadoInterConsulta= false;

function aplicarLocalUsuarioInterConsulta(forzar= false) {
    var localUsuario= obtenerLocalUsuarioInterConsulta();
    if (localUsuario == "") {
        return false;
    }

    if (!forzar && localUsuarioInicializadoInterConsulta) {
        return false;
    }

    if (!forzar && (valorCampoInterConsulta("inptBuscarInterConsulta7") != "" || valorCampoInterConsulta("inptFiltroRapidoLocalInterConsulta") != "")) {
        localUsuarioInicializadoInterConsulta= true;
        return false;
    }

    var aplicadoAvanzado= asignarValorSelectInterConsulta("inptBuscarInterConsulta7", localUsuario);
    var aplicadoRapido= asignarValorSelectInterConsulta("inptFiltroRapidoLocalInterConsulta", localUsuario);

    if (aplicadoAvanzado || aplicadoRapido) {
        localUsuarioInicializadoInterConsulta= true;
        actualizarResumenControlesInterConsulta();
        return true;
    }
    return false;
}

function textoOpcionSeleccionadaInterConsulta(id) {
    var elemento= document.getElementById(id);
    if (!elemento || elemento.value == "" || elemento.selectedIndex < 0) {
        return "";
    }
    var opcion= elemento.options[elemento.selectedIndex];
    return opcion ? (opcion.textContent || opcion.innerText || elemento.value).trim() : elemento.value;
}

function actualizarResumenControlesInterConsulta() {
    var resumen= document.getElementById("resumenControlesInterConsulta");
    if (!resumen) {
        return;
    }

    var filtros= [];
    var busqueda= valorCampoInterConsulta("inptBuscarInterConsultaGlobal").trim();
    if (busqueda != "") {
        filtros.push('Busqueda: "' + (busqueda.length > 32 ? busqueda.substring(0, 32) + "..." : busqueda) + '"');
    }

    [
        {id: "inptFiltroRapidoEstadoInterConsulta", etiqueta: "Estado"},
        {id: "inptFiltroRapidoTipoInterConsulta", etiqueta: "Subtipo"},
        {id: "inptFiltroRapidoLocalInterConsulta", etiqueta: "Local"}
    ].forEach(function(campo) {
        var texto= textoOpcionSeleccionadaInterConsulta(campo.id);
        if (texto != "") {
            filtros.push(campo.etiqueta + ": " + texto);
        }
    });

    var responsable= valorCampoInterConsulta("inptBuscarInterConsulta6").trim();
    if (responsable != "") {
        filtros.push("Responsable: " + (responsable.length > 32 ? responsable.substring(0, 32) + "..." : responsable));
    }

    var fechaDesde= valorCampoInterConsulta("inptBuscarInterConsultaFechaDesde");
    var fechaHasta= valorCampoInterConsulta("inptBuscarInterConsultaFechaHasta");
    if (fechaDesde != "" && fechaHasta != "" && fechaDesde == fechaHasta) {
        filtros.push("Fecha: " + formatearFechaCortaInterConsulta(fechaDesde));
    } else if (fechaDesde != "") {
        filtros.push("Desde: " + fechaDesde);
    } else if (fechaHasta != "") {
        filtros.push("Hasta: " + fechaHasta);
    }

    resumen.textContent= filtros.length > 0 ? filtros.join(" · ") : "Sin filtros aplicados";
    resumen.title= resumen.textContent;
}

function establecerControlesInterConsultaColapsados(colapsado) {
    var panel= document.getElementById("panelControlesInterConsulta");
    var contenido= document.getElementById("contenidoControlesInterConsulta");
    var boton= document.getElementById("btnAlternarControlesInterConsulta");
    var ventana= document.getElementById("divListadoInterConsulta");
    if (!panel || !contenido || !boton) {
        return;
    }

    colapsado= !!colapsado;
    panel.classList.toggle("is-collapsed", colapsado);
    contenido.hidden= colapsado;
    boton.setAttribute("aria-expanded", colapsado ? "false" : "true");
    boton.title= colapsado ? "Mostrar busqueda y filtros" : "Ocultar busqueda y filtros";
    var textoBoton= document.getElementById("textoAlternarControlesInterConsulta");
    if (textoBoton) {
        textoBoton.textContent= colapsado ? "Ver filtros" : "Ocultar filtros";
    }
    if (ventana) {
        ventana.classList.toggle("interconsulta-filters-collapsed", colapsado);
    }
    actualizarResumenControlesInterConsulta();
}

function alternarControlesInterConsulta() {
    var panel= document.getElementById("panelControlesInterConsulta");
    if (!panel) {
        return;
    }
    establecerControlesInterConsultaColapsados(!panel.classList.contains("is-collapsed"));
}

function inicializarControlesInterConsulta() {
    establecerControlesInterConsultaColapsados(true);
    inicializarFechaRapidaInterConsulta();
}

function clonarOpcionesInterConsulta(origenId, destinoId, usarTextoComoValor= false) {
    const origen= document.getElementById(origenId);
    const destino= document.getElementById(destinoId);
    if (!origen || !destino) {
        return;
    }

    const valorActual= destino.value;
    const nuevasOpciones= [];
    Array.from(origen.options || []).forEach(function(option) {
        const texto= (option.textContent || option.innerText || "").trim();
        const valor= texto.toLowerCase() == "todos" ? "" : (usarTextoComoValor ? texto : option.value);
        if (texto == "") {
            return;
        }
        const nuevaOpcion= document.createElement("option");
        nuevaOpcion.value= valor || "";
        nuevaOpcion.textContent= texto;
        nuevasOpciones.push(nuevaOpcion);
    });

    if (nuevasOpciones.length > 0) {
        destino.innerHTML= "";
        nuevasOpciones.forEach(function(option) {
            destino.appendChild(option);
        });
        destino.value= valorActual;
    }
}

function sincronizarOpcionesRapidasInterConsulta() {
    actualizarOpcionesSubtipoInterConsulta();
    clonarOpcionesInterConsulta("inptBuscarInterConsulta7", "inptFiltroRapidoLocalInterConsulta");
    aplicarLocalUsuarioInterConsulta(false);
    inicializarFechaRapidaInterConsulta();
}

function sincronizarFiltrosInterConsultaDesdeBarra() {
    sincronizarOpcionesRapidasInterConsulta();
    asignarValorCampoInterConsulta("inptBuscarInterConsulta5", valorCampoInterConsulta("inptFiltroRapidoEstadoInterConsulta"));
    asignarValorCampoInterConsulta("inptBuscarInterConsulta4", valorCampoInterConsulta("inptFiltroRapidoTipoInterConsulta"));
    asignarValorCampoInterConsulta("inptBuscarInterConsulta7", valorCampoInterConsulta("inptFiltroRapidoLocalInterConsulta"));
    if (document.getElementById("inptFiltroRapidoResponsableInterConsulta")) {
        asignarValorCampoInterConsulta("inptBuscarInterConsulta6", valorCampoInterConsulta("inptFiltroRapidoResponsableInterConsulta"));
    }
    actualizarVistaFechaRapidaInterConsulta();
    actualizarResumenControlesInterConsulta();
}

function sincronizarFiltrosInterConsultaDesdeAvanzado() {
    sincronizarOpcionesRapidasInterConsulta();
    asignarValorCampoInterConsulta("inptFiltroRapidoEstadoInterConsulta", valorCampoInterConsulta("inptBuscarInterConsulta5"));
    asignarValorCampoInterConsulta("inptFiltroRapidoTipoInterConsulta", valorCampoInterConsulta("inptBuscarInterConsulta4"));
    asignarValorCampoInterConsulta("inptFiltroRapidoLocalInterConsulta", valorCampoInterConsulta("inptBuscarInterConsulta7"));
    if (document.getElementById("inptFiltroRapidoResponsableInterConsulta")) {
        asignarValorCampoInterConsulta("inptFiltroRapidoResponsableInterConsulta", valorCampoInterConsulta("inptBuscarInterConsulta6"));
    }
    var fechaDesde= valorCampoInterConsulta("inptBuscarInterConsultaFechaDesde");
    var fechaHasta= valorCampoInterConsulta("inptBuscarInterConsultaFechaHasta");
    if (fechaDesde == "" && fechaHasta == "") {
        aplicarFechaRapidaInterConsulta(obtenerFechaActualIsoInterConsulta(), false);
    } else if (fechaDesde == fechaHasta) {
        asignarValorCampoInterConsulta("inptFiltroRapidoFechaInterConsulta", fechaDesde);
    } else {
        asignarValorCampoInterConsulta("inptFiltroRapidoFechaInterConsulta", "");
    }
    actualizarVistaFechaRapidaInterConsulta();
    actualizarChipActivoInterConsulta();
    actualizarResumenControlesInterConsulta();
}

function abrirFiltrosAvanzadosInterConsulta() {
    sincronizarFiltrosInterConsultaDesdeBarra();
    const overlay= document.getElementById("overlayFiltrosInterConsulta");
    if (overlay) {
        overlay.style.display= "";
    }
}

function aplicarFiltrosInterConsultaDesdeBarra() {
    sincronizarFiltrosInterConsultaDesdeBarra();
    actualizarChipActivoInterConsulta();
    buscarPacientesConInterConsultas();
}

function aplicarFiltrosAvanzadosInterConsulta() {
    sincronizarFiltrosInterConsultaDesdeAvanzado();
    const overlay= document.getElementById("overlayFiltrosInterConsulta");
    if (overlay) {
        overlay.style.display= "none";
    }
    buscarPacientesConInterConsultas();
}

function limpiarFiltrosInterConsulta(ejecutarBusqueda= true) {
    [
        "inptBuscarInterConsultaGlobal",
        "inptBuscarInterConsulta1",
        "inptBuscarInterConsulta2",
        "inptBuscarInterConsulta3",
        "inptBuscarInterConsulta4",
        "inptBuscarInterConsulta5",
        "inptBuscarInterConsulta6",
        "inptBuscarInterConsulta7",
        "inptUsuariosInterConsulta",
        "inptBuscarInterConsultaFechaDesde",
        "inptBuscarInterConsultaFechaHasta",
        "inptFiltroRapidoEstadoInterConsulta",
        "inptFiltroRapidoTipoInterConsulta",
        "inptFiltroRapidoLocalInterConsulta",
        "inptFiltroRapidoResponsableInterConsulta",
        "inptFiltroRapidoFechaInterConsulta"
    ].forEach(function(id) {
        asignarValorCampoInterConsulta(id, "");
    });

    localUsuarioInicializadoInterConsulta= false;
    aplicarLocalUsuarioInterConsulta(true);
    aplicarFechaRapidaInterConsulta(obtenerFechaActualIsoInterConsulta(), false);

    const ocultarInactivos= document.getElementById("inptSeleccFiltroEstadoInterConsulta");
    if (ocultarInactivos) {
        ocultarInactivos.checked= true;
    }

    actualizarChipActivoInterConsulta();
    actualizarResumenControlesInterConsulta();
    if (ejecutarBusqueda) {
        buscarPacientesConInterConsultas();
    }
}

function aplicarChipInterConsulta(campo, valor= "") {
    if (campo == "todos") {
        asignarValorCampoInterConsulta("inptFiltroRapidoEstadoInterConsulta", "");
    } else if (campo == "estado") {
        asignarValorCampoInterConsulta("inptFiltroRapidoEstadoInterConsulta", valor);
    } else if (campo == "tipo") {
        asignarValorCampoInterConsulta("inptFiltroRapidoTipoInterConsulta", valor);
        asignarValorCampoInterConsulta("inptFiltroRapidoEstadoInterConsulta", "");
    }

    aplicarFiltrosInterConsultaDesdeBarra();
}

function actualizarChipActivoInterConsulta() {
    const estado= valorCampoInterConsulta("inptFiltroRapidoEstadoInterConsulta") || valorCampoInterConsulta("inptBuscarInterConsulta5");
    let activo= "todos";
    if (estado != "") {
        activo= "estado:" + estado;
    }

    document.querySelectorAll(".interconsulta-filter-chip").forEach(function(chip) {
        chip.classList.toggle("is-active", chip.getAttribute("data-interconsulta-chip") == activo);
    });
}

function textoDetalleHilo(valor, fallback= "Sin dato") {
    const texto= (valor === null || valor === undefined) ? "" : String(valor).trim();
    return texto == "" || texto.toLowerCase() == "null" ? fallback : texto;
}

function numeroSeguimientoHilo(valor) {
    if (valor === null || valor === undefined || valor === "") {
        return 0;
    }
    const numero= Number(String(valor).replace(/\./g, "").replace(",", "."));
    return Number.isFinite(numero) ? numero : 0;
}

function formatearMontoSeguimientoHilo(valor) {
    const numero= Math.round(numeroSeguimientoHilo(valor));
    try {
        return "Gs. " + numero.toLocaleString("es-PY", { maximumFractionDigits: 0 });
    } catch (error) {
        return "Gs. " + String(numero).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
}

function formatearFechaSeguimientoHilo(fecha) {
    const texto= textoDetalleHilo(fecha, "");
    const partes= texto.substring(0, 10).split("-");
    if (partes.length == 3 && partes[0].length == 4) {
        return partes[2] + "/" + partes[1] + "/" + partes[0];
    }
    return texto;
}

function unirDetalleSeguimientoHilo(partes) {
    return partes
        .map(function(parte) {
            return textoDetalleHilo(parte, "");
        })
        .filter(function(parte) {
            return parte != "";
        })
        .join(" - ");
}

function tituloAsuntoHilo(asunto) {
    const texto= textoDetalleHilo(asunto, "Sin asunto");
    if (texto == texto.toUpperCase()) {
        const normalizado= texto.toLowerCase();
        return normalizado.charAt(0).toUpperCase() + normalizado.slice(1);
    }
    return texto;
}

function mostrarBadgeDetalleHilo(id, mostrar, texto= "") {
    const elemento= document.getElementById(id);
    if (!elemento) {
        return;
    }
    if (texto) {
        elemento.textContent= texto;
    }
    elemento.style.display= mostrar ? "" : "none";
}

function mostrarMotivoSenalizacionHilo(elemento, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    if (!elemento) {
        return false;
    }
    const titulo= textoDetalleHilo(elemento.getAttribute("data-hilo-alert-title") || elemento.textContent, "Senalizacion");
    const detalle= textoDetalleHilo(elemento.getAttribute("data-hilo-alert-detail") || elemento.getAttribute("title"), "No se encontro el motivo de la senalizacion.");
    if (typeof ver_vetana_informativa == "function") {
        ver_vetana_informativa(titulo, detalle, "info");
    } else {
        alert(titulo + "\n\n" + detalle);
    }
    return false;
}

function manejarInteraccionSenalizacionHilo(event) {
    const objetivo= event.target && event.target.closest ? event.target.closest("[data-hilo-alert]") : null;
    if (!objetivo) {
        return;
    }
    if (event.type == "keydown") {
        const tecla= event.key || event.keyCode;
        if (!(tecla == "Enter" || tecla == " " || tecla == 13 || tecla == 32)) {
            return;
        }
    }
    mostrarMotivoSenalizacionHilo(objetivo, event);
}

if (!window.senalizacionHiloEventosInicializados) {
    document.addEventListener("click", manejarInteraccionSenalizacionHilo, true);
    document.addEventListener("keydown", manejarInteraccionSenalizacionHilo, true);
    window.senalizacionHiloEventosInicializados= true;
}

var ventasSelectorSeguimientoInterConsulta= [];
var pacienteSelectorSeguimientoInterConsulta= {};

function escaparHtmlSeguimientoHilo(valor) {
    var texto= valor === null || valor === undefined ? "" : String(valor);
    return texto
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function manejarAccionSeguimientoHilo(event) {
    var objetivo= event.target && event.target.closest ? event.target.closest("[data-hilo-action]") : null;
    if (!objetivo) {
        return;
    }
    if (event.type == "keydown") {
        var tecla= event.key || event.keyCode;
        if (!(tecla == "Enter" || tecla == " " || tecla == 13 || tecla == 32)) {
            return;
        }
    }
    event.preventDefault();
    event.stopPropagation();
    if (event.stopImmediatePropagation) {
        event.stopImmediatePropagation();
    }

    var accion= objetivo.getAttribute("data-hilo-action") || "";
    if (accion == "abrir_cita") {
        abrirCitaSeguimientoDesdeHilo(objetivo);
    } else if (accion == "abrir_pago_mora") {
        abrirPagoMoraSeguimientoDesdeHilo(objetivo);
    }
}

if (!window.accionesSeguimientoHiloEventosInicializados) {
    document.addEventListener("click", manejarAccionSeguimientoHilo, true);
    document.addEventListener("keydown", manejarAccionSeguimientoHilo, true);
    window.accionesSeguimientoHiloEventosInicializados= true;
}

function abrirCitaSeguimientoDesdeHilo(elemento) {
    var idAgenda= textoDetalleHilo(elemento.getAttribute("data-agenda-id"), "");
    var fechaAgenda= textoDetalleHilo(elemento.getAttribute("data-agenda-fecha"), "");
    if (idAgenda == "") {
        mostrarMotivoSenalizacionHilo(elemento);
        return;
    }
    if (typeof cargarAgendaConsultoriosDesdePHP != "function" || typeof verDetalleAgenda != "function") {
        ver_vetana_informativa("Agenda no disponible", "No se encontro la funcion para abrir el detalle del turno.", "info");
        return;
    }

    var contenedorAgenda= document.getElementById("divAgendaConsultorios");
    if (contenedorAgenda) {
        contenedorAgenda.style.display= "";
    }
    asignarValorCampoInterConsulta("inptBuscarPacienteAgenda", "");
    asignarValorCampoInterConsulta("inptConsultorioAgendaFiltro", "");
    asignarValorCampoInterConsulta("inptEstadoAgenda", "");
    if (fechaAgenda != "") {
        asignarValorCampoInterConsulta("inptFechaAgenda", fechaAgenda);
    }
    if (typeof actualizarTextoFechaAgenda == "function") {
        actualizarTextoFechaAgenda();
    }
    if (typeof limpiarSeleccionConsultoriosAgenda == "function") {
        limpiarSeleccionConsultoriosAgenda();
    }

    cargarAgendaConsultoriosDesdePHP(function() {
        verDetalleAgenda(idAgenda);
    });
}

function abrirPagoMoraSeguimientoDesdeHilo(elemento) {
    cerrarSelectorVentasSeguimientoPaciente();
    var ventanaOrigen= obtenerContenedorSeguimientoDesdeElemento(elemento);
    var codInterConsulta= textoDetalleHilo(elemento.getAttribute("data-cod-interconsulta"), "");
    var pacienteFallback= {
        cod_cliente: textoDetalleHilo(elemento.getAttribute("data-cod-cliente"), ""),
        cedula: textoDetalleHilo(elemento.getAttribute("data-documento-paciente"), ""),
        nombre: textoDetalleHilo(elemento.getAttribute("data-nombre-paciente"), "")
    };

    if (codInterConsulta == "") {
        abrirHistorialVentaPacienteDesdeHilo(pacienteFallback, null);
        return;
    }

    var datos= new FormData();
    obtener_datos_user();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "buscarVentasSeguimientoPaciente");
    datos.append("cod_interConsulta", codInterConsulta);

    if (typeof verCerrarEfectoCargando == "function") {
        verCerrarEfectoCargando("1");
    }

    $.ajax({
        data: datos,
        url: "../php_system/abmInterConsulta.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        error: function(jqXHR, textstatus) {
            if (typeof verCerrarEfectoCargando == "function") {
                verCerrarEfectoCargando("");
            }
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmInterConsulta");
        },
        success: function(responseText) {
            if (typeof verCerrarEfectoCargando == "function") {
                verCerrarEfectoCargando("");
            }
            if (!hayVentanaSeguimientoVisible(ventanaOrigen)) {
                cerrarSelectorVentasSeguimientoPaciente();
                return;
            }
            try {
                var respuesta= typeof responseText == "string" ? $.parseJSON(responseText) : responseText;
                var estado= respuestaJqueryAjax(respuesta["1"]);
                if (!estado) {
                    var mensajeError= respuesta["2"] && respuesta["2"].mensaje ? respuesta["2"].mensaje : "No se pudieron consultar las ventas del paciente.";
                    ver_vetana_informativa("No se pudo abrir Pago/Mora", mensajeError, "info");
                    return;
                }

                var payload= respuesta["2"] || {};
                var paciente= payload.paciente || {};
                if (!paciente.cedula) {
                    paciente.cedula= pacienteFallback.cedula;
                }
                if (!paciente.nombre) {
                    paciente.nombre= pacienteFallback.nombre;
                }
                if (!paciente.cod_cliente) {
                    paciente.cod_cliente= pacienteFallback.cod_cliente;
                }

                var ventas= Array.isArray(payload.ventas) ? payload.ventas : [];
                if (ventas.length > 1) {
                    mostrarSelectorVentasSeguimientoPaciente(paciente, ventas);
                } else if (ventas.length == 1) {
                    abrirHistorialVentaPacienteDesdeHilo(paciente, ventas[0]);
                } else {
                    abrirHistorialVentaPacienteDesdeHilo(paciente, null);
                }
            } catch (error) {
                ver_vetana_informativa("No se pudo abrir Pago/Mora", "La respuesta del servidor no pudo interpretarse.", "info");
                var titulo= "Error: " + error + " \r\n Consola: " + responseText;
                if (typeof GuardarArchivosLog == "function") {
                    GuardarArchivosLog(titulo);
                }
            }
        }
    });
}

function obtenerModalSelectorVentasSeguimientoPaciente() {
    var modal= document.getElementById("modalSelectorVentasSeguimientoInterConsulta");
    if (modal) {
        return modal;
    }
    modal= document.createElement("div");
    modal.id= "modalSelectorVentasSeguimientoInterConsulta";
    modal.className= "interconsulta-sale-selector";
    modal.style.display= "none";
    modal.innerHTML=
        "<div class='interconsulta-sale-selector__backdrop' onclick='cerrarSelectorVentasSeguimientoPaciente()'></div>"
        +"<div class='interconsulta-sale-selector__panel' role='dialog' aria-modal='true' aria-labelledby='tituloSelectorVentasSeguimiento'>"
            +"<div class='interconsulta-sale-selector__header'>"
                +"<div>"
                    +"<p class='interconsulta-sale-selector__eyebrow'>Pago/Mora</p>"
                    +"<h3 id='tituloSelectorVentasSeguimiento'>Seleccionar venta a revisar</h3>"
                    +"<span id='subtituloSelectorVentasSeguimiento'></span>"
                +"</div>"
                +"<button type='button' class='interconsulta-sale-selector__close' onclick='cerrarSelectorVentasSeguimientoPaciente()' aria-label='Cerrar selector'>x</button>"
            +"</div>"
            +"<div class='interconsulta-sale-selector__body' id='bodySelectorVentasSeguimiento'></div>"
        +"</div>";
    document.body.appendChild(modal);
    return modal;
}

function obtenerContenedorSeguimientoDesdeElemento(elemento) {
    while (elemento && elemento !== document) {
        if (elemento.id == "divListadoInterConsulta" || elemento.id == "divAbmDetallesInterConsulta") {
            return elemento;
        }
        elemento= elemento.parentNode;
    }
    return null;
}

function estaVisibleContenedorSeguimiento(contenedor) {
    if (!contenedor || contenedor.style.display == "none") {
        return false;
    }
    if (typeof window.getComputedStyle == "function") {
        var estilos= window.getComputedStyle(contenedor);
        if (estilos.display == "none" || estilos.visibility == "hidden" || estilos.opacity == "0") {
            return false;
        }
    }
    return true;
}

function hayVentanaSeguimientoVisible(ventanaOrigen) {
    if (ventanaOrigen) {
        return estaVisibleContenedorSeguimiento(ventanaOrigen);
    }
    return estaVisibleContenedorSeguimiento(document.getElementById("divListadoInterConsulta"))
        || estaVisibleContenedorSeguimiento(document.getElementById("divAbmDetallesInterConsulta"));
}

function mostrarSelectorVentasSeguimientoPaciente(paciente, ventas) {
    if (!hayVentanaSeguimientoVisible()) {
        cerrarSelectorVentasSeguimientoPaciente();
        return;
    }
    ventasSelectorSeguimientoInterConsulta= ventas || [];
    pacienteSelectorSeguimientoInterConsulta= paciente || {};
    var modal= obtenerModalSelectorVentasSeguimientoPaciente();
    var subtitulo= document.getElementById("subtituloSelectorVentasSeguimiento");
    var body= document.getElementById("bodySelectorVentasSeguimiento");
    if (subtitulo) {
        subtitulo.textContent= unirDetalleSeguimientoHilo([
            pacienteSelectorSeguimientoInterConsulta.nombre || "Paciente",
            pacienteSelectorSeguimientoInterConsulta.cedula ? "CI " + pacienteSelectorSeguimientoInterConsulta.cedula : ""
        ]);
    }
    if (body) {
        var html= "";
        for (var i= 0; i < ventasSelectorSeguimientoInterConsulta.length; i++) {
            var venta= ventasSelectorSeguimientoInterConsulta[i] || {};
            var clase= numeroSeguimientoHilo(venta.cuotas_vencidas) > 0 ? "is-danger" : (numeroSeguimientoHilo(venta.cuotas_pendientes) > 0 ? "is-warning" : "is-muted");
            html += "<button type='button' class='interconsulta-sale-option " + clase + "' onclick='seleccionarVentaSeguimientoDesdeSelector(" + i + ")'>"
                +"<span class='interconsulta-sale-option__main'>"
                    +"<strong>Venta #" + escaparHtmlSeguimientoHilo(venta.cod_venta || "") + "</strong>"
                    +"<small>" + escaparHtmlSeguimientoHilo(venta.fecha_venta ? formatearFechaSeguimientoHilo(venta.fecha_venta) : "") + " - " + escaparHtmlSeguimientoHilo(venta.nombre_local || "") + "</small>"
                +"</span>"
                +"<span class='interconsulta-sale-option__status'>"
                    +"<b>" + escaparHtmlSeguimientoHilo(venta.estado || "Sin cuotas pendientes") + "</b>"
                    +"<small>" + escaparHtmlSeguimientoHilo(venta.saldo_pendiente_formato || "") + "</small>"
                +"</span>"
            +"</button>";
        }
        body.innerHTML= html || "<div class='interconsulta-sale-selector__empty'>No se encontraron ventas para seleccionar.</div>";
    }
    modal.style.display= "flex";
}

function cerrarSelectorVentasSeguimientoPaciente() {
    var modal= document.getElementById("modalSelectorVentasSeguimientoInterConsulta");
    if (modal) {
        modal.style.display= "none";
    }
    ventasSelectorSeguimientoInterConsulta= [];
    pacienteSelectorSeguimientoInterConsulta= {};
}

function seleccionarVentaSeguimientoDesdeSelector(indice) {
    var venta= ventasSelectorSeguimientoInterConsulta[indice];
    var paciente= pacienteSelectorSeguimientoInterConsulta;
    if (!venta) {
        return;
    }
    cerrarSelectorVentasSeguimientoPaciente();
    abrirHistorialVentaPacienteDesdeHilo(paciente, venta);
}

function abrirHistorialVentaPacienteDesdeHilo(paciente, venta) {
    cerrarSelectorVentasSeguimientoPaciente();
    paciente= paciente || {};
    var documento= textoDetalleHilo(paciente.cedula || paciente.documento || "", "");
    var nombre= textoDetalleHilo(paciente.nombre || "", "");
    var panelHistorial= document.getElementById("divHistorialVenta");
    if (typeof verCerrarHistorialVenta != "function" || !panelHistorial) {
        ver_vetana_informativa("Historial de ventas no disponible", "No se encontro la ventana de historial de ventas.", "info");
        return;
    }

    if (panelHistorial.style.display != "") {
        verCerrarHistorialVenta();
    }
    if (typeof limpiarcamposhistorialventa == "function") {
        limpiarcamposhistorialventa();
    }

    asignarValorCampoInterConsulta("inptBuscarHistorialVenta3", documento);
    if (documento == "") {
        asignarValorCampoInterConsulta("inptBuscarHistorialVenta4", nombre);
    }
    var checkTodos= document.getElementById("inptCheckHistorialVenta2");
    var checkRango= document.getElementById("inptCheckHistorialVenta1");
    if (checkTodos) {
        checkTodos.checked= true;
    }
    if (checkRango) {
        checkRango.checked= false;
    }

    if (typeof buscarhistorialventa == "function") {
        buscarhistorialventa(function() {
            if (venta && venta.cod_venta) {
                setTimeout(function() {
                    seleccionarVentaHistorialDesdeHilo(venta.cod_venta, true);
                }, 60);
            }
        });
    }
}

function seleccionarVentaHistorialDesdeHilo(codVenta, abrirCreditos) {
    var filas= document.querySelectorAll("#table_historial_venta tr[id='tbSelecRegistro']");
    for (var i= 0; i < filas.length; i++) {
        var celda= filas[i].querySelector('td[id="td_datos_8"]');
        if (celda && String(celda.textContent || celda.innerText || "").trim() == String(codVenta)) {
            obtenerelementohistroialventa(filas[i]);
            if (abrirCreditos && typeof verCerrarVentanasHistorialVenta == "function") {
                verCerrarVentanasHistorialVenta(2);
            }
            return true;
        }
    }

    if (typeof mostrarMensajeTabHistorialVenta == "function") {
        mostrarMensajeTabHistorialVenta("Historial del paciente abierto. No se encontro la venta seleccionada en la primera carga.");
    }
    return false;
}

function inicialesParticipanteHilo(texto) {
    const limpio= textoDetalleHilo(texto, "")
        .replace(/\([^)]*\)/g, " ")
        .replace(/[^a-zA-Z\u00C0-\u00FF0-9\s]/g, " ")
        .replace(/\s+/g, " ")
        .trim();
    if (limpio == "") {
        return "?";
    }

    const partes= limpio.split(" ").filter(Boolean);
    if (partes.length == 1) {
        return partes[0].slice(0, 2).toUpperCase();
    }
    return (partes[0].charAt(0) + partes[partes.length - 1].charAt(0)).toUpperCase();
}

function tooltipParticipanteHilo(texto) {
    return textoDetalleHilo(texto, "Participante")
        .replace(/\s*\(([^)]+)\)\s*$/, " - $1")
        .replace(/\s+/g, " ")
        .trim();
}

function actualizarAvatarStackDetalleHilo() {
    const contenedor= document.getElementById("avatarStackDetalle");
    const listado= document.getElementById("listadoMencionados");
    if (!contenedor || !listado) {
        return;
    }

    contenedor.innerHTML= "";
    const participantes= Array.from(listado.querySelectorAll(".interconsulta-participant-item"))
        .filter(function(item) {
            return item.style.display != "none";
        })
        .map(function(item) {
            const nombre= item.querySelector(".interconsulta-participant-info span:not(.interconsulta-participant-avatar)");
            return nombre ? nombre.textContent.trim() : item.textContent.trim();
        })
        .filter(function(nombre) {
            return nombre != "";
        });

    if (participantes.length == 0) {
        contenedor.style.display= "none";
        return;
    }

    contenedor.style.display= "";
    const maximoVisible= 5;
    participantes.slice(0, maximoVisible).forEach(function(nombre) {
        const avatar= document.createElement("span");
        avatar.className= "interconsulta-avatar-stack__item";
        avatar.textContent= inicialesParticipanteHilo(nombre);
        avatar.title= tooltipParticipanteHilo(nombre);
        contenedor.appendChild(avatar);
    });

    if (participantes.length > maximoVisible) {
        const resto= document.createElement("span");
        resto.className= "interconsulta-avatar-stack__item interconsulta-avatar-stack__more";
        resto.textContent= "+" + (participantes.length - maximoVisible);
        resto.title= participantes.slice(maximoVisible).map(tooltipParticipanteHilo).join(", ");
        contenedor.appendChild(resto);
    }
}

function actualizarAvisosSeguimientoPacienteDetalle(datosHilo) {
    const resumen= document.getElementById("contenedorEncabezadoInterConsulta");
    if (!resumen) {
        return;
    }

    let contenedor= document.getElementById("avisosSeguimientoPacienteInterConsulta");
    if (!contenedor) {
        contenedor= document.createElement("div");
        contenedor.id= "avisosSeguimientoPacienteInterConsulta";
        contenedor.className= "interconsulta-patient-alerts";
        const titulo= resumen.querySelector(".card-title");
        if (titulo && titulo.parentNode) {
            titulo.parentNode.insertBefore(contenedor, titulo.nextSibling);
        } else {
            resumen.appendChild(contenedor);
        }
    }

    contenedor.innerHTML= "";
    const esSeguimiento= Number(datosHilo.esSeguimientoPaciente || 0) > 0;
    const cedula= textoDetalleHilo(datosHilo.cedula, "");
    const conflicto= Number(datosHilo.seguimiento_conflicto || 0) > 0;
    if (!esSeguimiento && !conflicto && cedula == "") {
        contenedor.style.display= "none";
        return;
    }

    const totalVentas= Number(datosHilo.total_ventas_paciente || 0);
    const totalPlanes= Number(datosHilo.total_planes_madre || 0);
    const ventasSinPlan= Number(datosHilo.ventas_sin_plan_madre || 0);
    const detalleConflicto= textoDetalleHilo(datosHilo.seguimiento_detalle_conflicto, "");
    const totalLocales= Number(datosHilo.seguimiento_total_locales || 0);
    const localesSeguimiento= textoDetalleHilo(datosHilo.seguimiento_locales, "");
    const totalCreditos= numeroSeguimientoHilo(datosHilo.seguimiento_total_creditos);
    const cuotasPendientes= numeroSeguimientoHilo(datosHilo.seguimiento_cuotas_pendientes);
    const cuotasVencidas= numeroSeguimientoHilo(datosHilo.seguimiento_cuotas_vencidas);
    const diasMora= numeroSeguimientoHilo(datosHilo.seguimiento_dias_mora);
    const saldoPendiente= numeroSeguimientoHilo(datosHilo.seguimiento_saldo_pendiente);
    const proximaCuota= textoDetalleHilo(datosHilo.seguimiento_proxima_cuota_fecha, "");
    const citasFuturas= numeroSeguimientoHilo(datosHilo.seguimiento_citas_futuras);
    const citaFecha= textoDetalleHilo(datosHilo.seguimiento_proxima_cita_fecha, "");
    const citaHora= textoDetalleHilo(datosHilo.seguimiento_proxima_cita_hora, "");
    const citaEstado= textoDetalleHilo(datosHilo.seguimiento_proxima_cita_estado, "");
    const citaMotivo= textoDetalleHilo(datosHilo.seguimiento_proxima_cita_motivo, "");
    const citaProfesional= textoDetalleHilo(datosHilo.seguimiento_proxima_cita_profesional, "");
    const planesDefinitivos= numeroSeguimientoHilo(datosHilo.seguimiento_planes_definitivos);
    const estadosPlanes= textoDetalleHilo(datosHilo.seguimiento_estado_planes, "");

    function agregarAviso(clase, titulo, texto) {
        const aviso= document.createElement("div");
        aviso.className= "interconsulta-patient-alert interconsulta-patient-alert--" + clase;
        aviso.setAttribute("title", texto);
        aviso.setAttribute("tabindex", "0");
        aviso.setAttribute("role", "button");
        aviso.setAttribute("data-hilo-alert", "1");
        aviso.setAttribute("data-hilo-alert-title", titulo);
        aviso.setAttribute("data-hilo-alert-detail", texto);
        const fuerte= document.createElement("strong");
        fuerte.textContent= titulo;
        const descripcion= document.createElement("span");
        descripcion.textContent= texto;
        aviso.appendChild(fuerte);
        aviso.appendChild(descripcion);
        contenedor.appendChild(aviso);
    }

    if (esSeguimiento) {
        agregarAviso(
            "info",
            "Hilo maestro por cedula",
            "CI " + (cedula || "sin dato") + " - " + totalVentas + " venta(s) real(es) vinculada(s) y " + totalPlanes + " plan(es) madre."
        );
    }

    if (conflicto) {
        agregarAviso(
            "conflict",
            "Conflicto CI",
            detalleConflicto || "Esta cedula esta asociada a mas de un paciente y debe ser revisada."
        );
    }

    if (!esSeguimiento) {
        contenedor.style.display= "";
        return;
    }

    if (totalLocales > 1) {
        agregarAviso(
            "conflict",
            "Varios locales",
            localesSeguimiento || "Esta cedula tiene ventas vinculadas en mas de un local."
        );
    }

    if (ventasSinPlan > 0) {
        agregarAviso(
            "warning",
            "Sin plan madre",
            ventasSinPlan + " venta(s) real(es) del paciente no estan vinculadas a un plan madre. Es solo un aviso; no bloquea el flujo."
        );
    }

    if (cuotasVencidas > 0) {
        agregarAviso(
            "warning",
            "Alerta financiera",
            unirDetalleSeguimientoHilo([
                cuotasVencidas + " cuota(s) vencida(s)",
                diasMora + " dia(s) de mora",
                formatearMontoSeguimientoHilo(saldoPendiente)
            ])
        );
    } else if (cuotasPendientes > 0) {
        agregarAviso(
            "warning",
            "Alerta financiera",
            unirDetalleSeguimientoHilo([
                cuotasPendientes + " cuota(s) pendiente(s)",
                formatearMontoSeguimientoHilo(saldoPendiente),
                proximaCuota ? "Proxima cuota " + formatearFechaSeguimientoHilo(proximaCuota) : ""
            ])
        );
    } else if (totalCreditos > 0) {
        agregarAviso(
            "success",
            "Cuenta saldada",
            "Tiene cuotas registradas y no registra saldo pendiente."
        );
    } else {
        agregarAviso(
            "muted",
            "Sin cuotas registradas",
            "No se encontraron cuotas vinculadas a las ventas del hilo."
        );
    }

    if (citaFecha != "") {
        agregarAviso(
            citaEstado.toUpperCase().indexOf("DEUDA") >= 0 ? "warning" : "info",
            "Proxima cita",
            unirDetalleSeguimientoHilo([
                formatearFechaSeguimientoHilo(citaFecha) + (citaHora ? " " + citaHora : ""),
                citaEstado,
                citaProfesional,
                citaMotivo,
                citasFuturas > 1 ? citasFuturas + " cita(s) futuras" : ""
            ])
        );
    } else {
        agregarAviso(
            "warning",
            "Sin cita agendada",
            "No se encontro una cita futura activa para este paciente."
        );
    }

    if (planesDefinitivos > 0) {
        agregarAviso(
            "info",
            "Plan definitivo",
            unirDetalleSeguimientoHilo([
                planesDefinitivos + " plan(es) registrado(s)",
                estadosPlanes ? "Estado: " + estadosPlanes : ""
            ])
        );
    }

    contenedor.style.display= "";
}

function actualizarCabeceraDetalleHilo(datosHilo, opcionesDictamen= "") {
    if (!datosHilo) {
        return;
    }

    const codigo= textoDetalleHilo(datosHilo.cod_interConsulta, "-");
    const asunto= tituloAsuntoHilo(datosHilo.asunto);
    const estado= textoDetalleHilo(datosHilo.estado, "Sin estado");
    const esSeguimientoPaciente= Number(datosHilo.esSeguimientoPaciente || 0) > 0;
    const tipo= textoDetalleHilo(datosHilo.tipo, esSeguimientoPaciente ? "Sin subtipo" : "Sin tipo");
    const local= textoDetalleHilo(datosHilo.nombre_local, "Sin local");
    const ventaCodigo= textoDetalleHilo(datosHilo.cod_ventaFK || datosHilo.num_factura, "");
    const ventaComprobante= textoDetalleHilo(datosHilo.num_factura, "");
    const ventaPaciente= textoDetalleHilo(datosHilo.nombre_persona, "");
    const ventaCedula= textoDetalleHilo(datosHilo.cedula, "");
    const ventaApodo= textoDetalleHilo(datosHilo.apodo_venta || datosHilo.apodo, "");
    let venta= "Sin venta asociada";
    if (ventaCodigo) {
        venta= ventaCodigo;
        if (ventaPaciente) {
            venta += " - " + ventaPaciente;
        }
        if (ventaCedula) {
            venta += " - CI " + ventaCedula;
        }
        if (ventaComprobante && ventaComprobante != ventaCodigo) {
            venta += " - Comp. " + ventaComprobante;
        }
        if (ventaApodo) {
            venta += " (" + ventaApodo + ")";
        }
    } else if (esSeguimientoPaciente) {
        venta= "Seguimiento por cedula";
        if (ventaCedula) {
            venta += " - CI " + ventaCedula;
        }
        if (Number(datosHilo.total_ventas_paciente || 0) > 0) {
            venta += " - " + Number(datosHilo.total_ventas_paciente || 0) + " venta(s)";
        }
    }
    const monto= textoDetalleHilo(datosHilo.monto_limite, "Sin monto limite");
    const pendiente= Number(datosHilo.cantMensajesNoLeidos || 0) > 0;
    const codVentaFK= textoDetalleHilo(datosHilo.cod_ventaFK, "");
    const vinculado= Number(datosHilo.cantAsociadoGastos || 0) > 0 || (codVentaFK != "" && codVentaFK != "0") || esSeguimientoPaciente;
    const puedeUnificarSeguimiento= (codVentaFK != "" && codVentaFK != "0") || esSeguimientoPaciente;
    const tieneDictamen= String(opcionesDictamen || "").trim() != "";

    const tituloDetalleHilo= "Hilo #" + codigo + " " + String.fromCharCode(8212) + " " + asunto;
    const tituloDetalle= document.getElementById("tituloInterConsultas");
    tituloDetalle.textContent= tituloDetalleHilo;
    tituloDetalle.title= tituloDetalleHilo;
    document.getElementById("tituloInterConsultas2").textContent= asunto;
    document.getElementById("txtUsuarioCreadorInterConsulta").textContent= textoDetalleHilo(datosHilo.nombre_persona_creador, "Sin creador");
    document.getElementById("txtFechaCreadorInterConsulta").textContent= textoDetalleHilo(datosHilo.fecha_creacion, "Sin fecha");
    document.getElementById("txtEstadoInterConsulta").textContent= estado;
    document.getElementById("txtTipoInterConsulta").textContent= tipo;
    document.getElementById("txtCodInterConsulta").textContent= codigo;
    document.getElementById("localDetalleInterConsulta").textContent= local;
    document.getElementById("txtCodVenta").textContent= venta;
    document.getElementById("txtMontoLimite").textContent= monto == "Sin monto limite" ? monto : monto + " Gs.";

    const estadoClase= estado.toLowerCase().replace(/\s+/g, "-");
    document.getElementById("txtEstadoInterConsulta").className= "interconsulta-data-badge interconsulta-data-badge--" + estadoClase;
    document.getElementById("txtTipoInterConsulta").className= "interconsulta-data-badge";
    document.getElementById("badgeEstadoDetalle").className= "interconsulta-detail-badge interconsulta-detail-badge--" + estadoClase;
    document.getElementById("badgeEstadoDetalle").textContent= estado;
    document.getElementById("badgeTipoDetalle").textContent= tipo;
    document.getElementById("badgeLocalDetalle").textContent= local;
    mostrarBadgeDetalleHilo("badgePendienteDetalle", pendiente, "Sin responder");
    mostrarBadgeDetalleHilo("badgeVinculadoDetalle", vinculado, "Hilo vinculado");
    mostrarBadgeDetalleHilo("badgeDictamenDetalle", tieneDictamen, "Dictamen registrado");
    var btnUnificarSeguimiento= document.getElementById("btnUnificarSeguimientoPaciente");
    if (btnUnificarSeguimiento) {
        btnUnificarSeguimiento.style.display= puedeUnificarSeguimiento ? "" : "none";
        btnUnificarSeguimiento.disabled= !puedeUnificarSeguimiento;
    }
    if (Number(datosHilo.resumen_seguimiento_cargado || 0) > 0) {
        actualizarAvisosSeguimientoPacienteDetalle(datosHilo);
    } else {
        const avisosSeguimiento= document.getElementById("avisosSeguimientoPacienteInterConsulta");
        if (avisosSeguimiento) {
            avisosSeguimiento.innerHTML= "";
            avisosSeguimiento.style.display= "none";
        }
    }

    const resumen= document.getElementById("contenedorEncabezadoInterConsulta");
    if (resumen) {
        resumen.classList.toggle("is-pending", pendiente);
        resumen.classList.toggle("is-linked", vinculado);
        resumen.classList.toggle("is-patient-master", esSeguimientoPaciente);
        resumen.classList.toggle("is-patient-conflict", Number(datosHilo.seguimiento_conflicto || 0) > 0);
    }

    const usuarioMensaje= document.getElementById("nombreUsuarioMensaje");
    const usuarioActual= document.getElementById("lblUser");
    if (usuarioMensaje && usuarioActual) {
        usuarioMensaje.textContent= usuarioActual.textContent.trim();
    }
}

var interConsultaMarcandoLectura= false;
var ultimoCodInterConsultaLectura= "";
var ultimoMomentoInterConsultaLectura= 0;

function tieneIndicadorLecturaPendienteInterConsulta() {
    const badgePendiente= document.getElementById("badgePendienteDetalle");
    const avisoPendiente= document.getElementById("avisoMensajesPendientesInterConsulta");
    const resumen= document.getElementById("contenedorEncabezadoInterConsulta");
    const filaSeleccionada= document.querySelector("#table_frm_VistaInterConsulta .interconsulta-thread-row--selected");

    return (badgePendiente && badgePendiente.style.display != "none")
        || (avisoPendiente && avisoPendiente.style.display != "none")
        || (resumen && resumen.classList.contains("is-pending"))
        || (filaSeleccionada && filaSeleccionada.classList.contains("interconsulta-thread-row--pending"));
}

function actualizarVistaInterConsultaLeida() {
    mostrarBadgeDetalleHilo("badgePendienteDetalle", false);

    const avisoPendiente= document.getElementById("avisoMensajesPendientesInterConsulta");
    if (avisoPendiente) {
        avisoPendiente.style.display= "none";
    }

    const resumen= document.getElementById("contenedorEncabezadoInterConsulta");
    if (resumen) {
        resumen.classList.remove("is-pending");
    }

    const filaSeleccionada= document.querySelector("#table_frm_VistaInterConsulta .interconsulta-thread-row--selected");
    if (filaSeleccionada) {
        filaSeleccionada.classList.remove("interconsulta-thread-row--pending");
        const badgeFila= filaSeleccionada.querySelector(".interconsulta-pending-badge");
        if (badgeFila) {
            badgeFila.remove();
        }
        const cantidadNoLeida= filaSeleccionada.querySelector("#td_datos_14");
        if (cantidadNoLeida) {
            cantidadNoLeida.textContent= "0";
        }
    }
}

function marcarInterConsultaLeidaDesdeEditor() {
    if (!cod_interConsulta || interConsultaMarcandoLectura) {
        return;
    }

    const ahora= Date.now();
    const mismoHilo= String(ultimoCodInterConsultaLectura) == String(cod_interConsulta);
    if (!tieneIndicadorLecturaPendienteInterConsulta() && mismoHilo && (ahora - ultimoMomentoInterConsultaLectura) < 3000) {
        return;
    }

    interConsultaMarcandoLectura= true;
    obtener_datos_user();
    marcarMensajeLeido(true, function() {
        interConsultaMarcandoLectura= false;
        ultimoCodInterConsultaLectura= cod_interConsulta;
        ultimoMomentoInterConsultaLectura= Date.now();
    });
}

function inicializarLecturaEditorInterConsulta() {
    const editorMensaje= document.getElementById("inptContenidoAbmMensaje");
    if (editorMensaje && !editorMensaje.dataset.lecturaInterconsultaInicializada) {
        editorMensaje.dataset.lecturaInterconsultaInicializada= "1";
        editorMensaje.addEventListener("click", marcarInterConsultaLeidaDesdeEditor);
        editorMensaje.addEventListener("focus", marcarInterConsultaLeidaDesdeEditor);
    }
}

function mostrarEstadoResumenSeguimientoInterConsulta(texto, esError= false) {
    const resumen= document.getElementById("contenedorEncabezadoInterConsulta");
    if (!resumen) {return;}
    let contenedor= document.getElementById("avisosSeguimientoPacienteInterConsulta");
    if (!contenedor) {
        contenedor= document.createElement("div");
        contenedor.id= "avisosSeguimientoPacienteInterConsulta";
        contenedor.className= "interconsulta-patient-alerts";
        const titulo= resumen.querySelector(".card-title");
        if (titulo && titulo.parentNode) {
            titulo.parentNode.insertBefore(contenedor, titulo.nextSibling);
        } else {
            resumen.appendChild(contenedor);
        }
    }
    contenedor.style.display= "";
    contenedor.innerHTML= '<div class="interconsulta-flow-state'+(esError ? ' interconsulta-flow-state--error' : '')+'">'+texto+'</div>';
}

function cargarResumenSeguimientoInterConsulta() {
    const detalles= document.querySelector("#divAbmDetallesInterConsulta .interconsulta-thread-details");
    if (!detalles || !detalles.open || !cod_interConsulta) {return;}
    const codigoSolicitud= String(cod_interConsulta);
    if (detalles.dataset.resumenCargado == codigoSolicitud || detalles.dataset.resumenCargando == codigoSolicitud) {return;}

    detalles.dataset.resumenCargando= codigoSolicitud;
    mostrarEstadoResumenSeguimientoInterConsulta("Cargando resumen del paciente...");
    obtener_datos_user();
    const datos= new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "buscarResumenSeguimientoInterConsulta");
    datos.append("cod_interConsulta", codigoSolicitud);

    $.ajax({
        data: datos,
        url: "../php_system/abmInterConsulta.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        error: function () {
            if (String(cod_interConsulta) == codigoSolicitud) {
                mostrarEstadoResumenSeguimientoInterConsulta("No se pudo cargar el resumen del paciente.", true);
            }
        },
        success: function (responseText) {
            try {
                const respuesta= $.parseJSON(responseText);
                if (String(cod_interConsulta) != codigoSolicitud) {return;}
                if (respuesta["1"] == "exito") {
                    actualizarAvisosSeguimientoPacienteDetalle(respuesta["2"] || {});
                    detalles.dataset.resumenCargado= codigoSolicitud;
                } else {
                    mostrarEstadoResumenSeguimientoInterConsulta("No se pudo cargar el resumen del paciente.", true);
                }
            } catch (error) {
                if (String(cod_interConsulta) == codigoSolicitud) {
                    mostrarEstadoResumenSeguimientoInterConsulta("No se pudo cargar el resumen del paciente.", true);
                }
            }
        },
        complete: function () {
            if (detalles.dataset.resumenCargando == codigoSolicitud) {
                delete detalles.dataset.resumenCargando;
            }
        }
    });
}

function inicializarResumenDiferidoInterConsulta() {
    const detalles= document.querySelector("#divAbmDetallesInterConsulta .interconsulta-thread-details");
    if (!detalles || detalles.dataset.resumenDiferidoInicializado) {return;}
    detalles.dataset.resumenDiferidoInicializado= "1";
    detalles.addEventListener("toggle", function() {
        if (detalles.open) {
            cargarResumenSeguimientoInterConsulta();
        }
    });
}

function actualizarContadorMensajeInterConsulta() {
    const editorMensaje= document.getElementById("inptContenidoAbmMensaje");
    const contador= document.getElementById("limiteCaracteresMensajeInterconsulta");
    if (!editorMensaje || !contador) {
        return;
    }

    contador.innerText= editorMensaje.textContent.length;
}

function ajustarAlturaEditorMensajeInterConsulta() {
    const editorMensaje= document.getElementById("inptContenidoAbmMensaje");
    if (!editorMensaje) {
        return;
    }

    const alturaMinima= 44;
    const alturaMaxima= 112;
    editorMensaje.style.height= alturaMinima + "px";

    const alturaContenido= editorMensaje.scrollHeight;
    const alturaFinal= Math.min(Math.max(alturaContenido, alturaMinima), alturaMaxima);
    editorMensaje.style.height= alturaFinal + "px";
    editorMensaje.style.overflowY= alturaContenido > alturaMaxima ? "auto" : "hidden";
    actualizarContadorMensajeInterConsulta();
}

function inicializarComposerMensajeInterConsulta() {
    const editorMensaje= document.getElementById("inptContenidoAbmMensaje");
    if (!editorMensaje) {
        return;
    }

    if (!editorMensaje.dataset.composerInterconsultaInicializada) {
        editorMensaje.dataset.composerInterconsultaInicializada= "1";
        editorMensaje.addEventListener("input", ajustarAlturaEditorMensajeInterConsulta);
        editorMensaje.addEventListener("keyup", ajustarAlturaEditorMensajeInterConsulta);
        editorMensaje.addEventListener("paste", function() {
            setTimeout(ajustarAlturaEditorMensajeInterConsulta, 0);
        });
    }

    ajustarAlturaEditorMensajeInterConsulta();
}

function alternarOpcionesComposerInterConsulta(idPanel, boton) {
    const panel= document.getElementById(idPanel);
    if (!panel) {
        return;
    }

    const seAbrira= !panel.classList.contains("show");
    if (typeof bootstrap !== "undefined" && bootstrap.Collapse) {
        const instancia= bootstrap.Collapse.getOrCreateInstance(panel, { toggle: false });
        if (seAbrira) {
            instancia.show();
        } else {
            instancia.hide();
        }
    } else {
        panel.classList.toggle("show", seAbrira);
    }

    if (boton) {
        const textoAbrir= boton.getAttribute("data-text-open") || "Ver opciones";
        const textoCerrar= boton.getAttribute("data-text-close") || "Ocultar opciones";
        const etiqueta= boton.querySelector("[data-label]");
        if (etiqueta) {
            etiqueta.textContent= seAbrira ? textoCerrar : textoAbrir;
        }
        boton.setAttribute("aria-expanded", seAbrira ? "true" : "false");
    }
}

function manejarBusquedaGlobalInterConsulta(event) {
    if (event && event.keyCode == 13) {
        if (temporizadorBusquedaGlobalInterConsulta) {
            clearTimeout(temporizadorBusquedaGlobalInterConsulta);
        }
        aplicarFiltrosInterConsultaDesdeBarra();
        return;
    }

    if (temporizadorBusquedaGlobalInterConsulta) {
        clearTimeout(temporizadorBusquedaGlobalInterConsulta);
    }
    temporizadorBusquedaGlobalInterConsulta= setTimeout(function() {
        aplicarFiltrosInterConsultaDesdeBarra();
    }, 500);
}

document.addEventListener("DOMContentLoaded", function() {
    actualizarTabsCategoriaHilosInterConsulta();
    actualizarOpcionesSubtipoInterConsulta();
    sincronizarOpcionesRapidasInterConsulta();
    actualizarChipActivoInterConsulta();
    inicializarControlesInterConsulta();
    inicializarLecturaEditorInterConsulta();
    inicializarComposerMensajeInterConsulta();
    inicializarResumenDiferidoInterConsulta();
    inicializarSeguimientosProgramadosInterConsulta();
});
window.addEventListener("load", function() {
    actualizarTabsCategoriaHilosInterConsulta();
    actualizarOpcionesSubtipoInterConsulta();
    sincronizarOpcionesRapidasInterConsulta();
    actualizarChipActivoInterConsulta();
    inicializarControlesInterConsulta();
    inicializarLecturaEditorInterConsulta();
    inicializarComposerMensajeInterConsulta();
    inicializarResumenDiferidoInterConsulta();
    inicializarSeguimientosProgramadosInterConsulta();
});

function buscarPacientesConInterConsultas() {
    const cod_interC= document.getElementById('inptBuscarInterConsulta1').value;
    const asunto= document.getElementById('inptBuscarInterConsulta2').value;
    const nombre_responsable= document.getElementById('inptBuscarInterConsulta6').value;
    const nombre_cliente= document.getElementById('inptBuscarInterConsulta3').value;
    const estado= document.getElementById('inptBuscarInterConsulta5').value;
    const tipo= document.getElementById('inptBuscarInterConsulta4').value;
    const ocultar_inactivos= document.getElementById('inptSeleccFiltroEstadoInterConsulta').checked;
    const usuario_vinculado= document.getElementById('inptUsuariosInterConsulta').value;
    const cod_localFK= document.getElementById('inptBuscarInterConsulta7').value;
    const busqueda_global= valorCampoInterConsulta("inptBuscarInterConsultaGlobal");
    const fecha_desde= valorCampoInterConsulta("inptBuscarInterConsultaFechaDesde");
    const fecha_hasta= valorCampoInterConsulta("inptBuscarInterConsultaFechaHasta");
    const categoria_principal= obtenerCategoriaActivaInterConsulta();
    actualizarResumenControlesInterConsulta();
    
    buscarPacientesConInterConsultas2(cod_interC, asunto, nombre_responsable, nombre_cliente, estado, tipo, cod_localFK, userid, limiteMaximoListadoInterConsulta, ocultar_inactivos, usuario_vinculado, busqueda_global, fecha_desde, fecha_hasta, categoria_principal);
}

function actualizarActividadDiariaSeguimientoInterConsulta(html) {
    const contenedor= document.getElementById("panelActividadDiariaInterConsulta");
    if (!contenedor) {
        return;
    }
    contenedor.innerHTML= html || '<div class="interconsulta-daily-activity__empty">Sin gestiones hoy</div>';
}

function fechaLocalInputSeguimientoInterConsulta(fecha) {
    var valor= fecha instanceof Date ? new Date(fecha.getTime()) : new Date();
    valor.setSeconds(0, 0);
    var compensada= new Date(valor.getTime() - (valor.getTimezoneOffset() * 60000));
    return compensada.toISOString().slice(0, 16);
}

function fechaRapidaSeguimientoInterConsulta(dias, meses) {
    var ahora= new Date();
    var resultado= new Date(ahora.getTime());
    resultado.setSeconds(0, 0);
    if (meses) {
        var diaOriginal= resultado.getDate();
        resultado.setDate(1);
        resultado.setMonth(resultado.getMonth() + meses);
        var ultimoDia= new Date(resultado.getFullYear(), resultado.getMonth() + 1, 0).getDate();
        resultado.setDate(Math.min(diaOriginal, ultimoDia));
    } else {
        resultado.setDate(resultado.getDate() + (dias || 1));
    }
    return resultado;
}

function generarTokenSeguimientoInterConsulta() {
    return "sg-" + String(userid || "0") + "-" + Date.now().toString(36) + "-" + Math.random().toString(36).slice(2, 12);
}

function seleccionarFechaRapidaSeguimientoInterConsulta(boton) {
    var campo= document.getElementById("fechaProgramadaSeguimientoInterConsulta");
    if (!campo || !boton) {
        return;
    }
    document.querySelectorAll("#panelProgramarSeguimientoInterConsulta [data-followup-days], #panelProgramarSeguimientoInterConsulta [data-followup-months], #panelProgramarSeguimientoInterConsulta [data-followup-custom]").forEach(function(opcion) {
        var seleccionada= opcion === boton;
        opcion.classList.toggle("is-active", seleccionada);
        opcion.setAttribute("aria-pressed", seleccionada ? "true" : "false");
    });
    if (boton.getAttribute("data-followup-custom") == "1") {
        campo.focus();
        return;
    }
    var dias= parseInt(boton.getAttribute("data-followup-days") || "0", 10);
    var meses= parseInt(boton.getAttribute("data-followup-months") || "0", 10);
    campo.value= fechaLocalInputSeguimientoInterConsulta(fechaRapidaSeguimientoInterConsulta(dias, meses));
    actualizarResumenSeguimientoProgramadoInterConsulta();
}

function seleccionarFechaPersonalizadaSeguimientoInterConsulta() {
    document.querySelectorAll("#panelProgramarSeguimientoInterConsulta [data-followup-days], #panelProgramarSeguimientoInterConsulta [data-followup-months], #panelProgramarSeguimientoInterConsulta [data-followup-custom]").forEach(function(opcion) {
        var personalizada= opcion.getAttribute("data-followup-custom") == "1";
        opcion.classList.toggle("is-active", personalizada);
        opcion.setAttribute("aria-pressed", personalizada ? "true" : "false");
    });
    actualizarResumenSeguimientoProgramadoInterConsulta();
}

function obtenerPlantillaSeguimientoInterConsulta(idPlantilla, listado) {
    var id= String(idPlantilla || "");
    var plantillas= Array.isArray(listado) ? listado : plantillasSeguimientoInterConsulta;
    for (var i= 0; i < plantillas.length; i++) {
        if (String(plantillas[i].id_plantilla || "") == id) {
            return plantillas[i];
        }
    }
    return null;
}

function renderizarOpcionesSeguimientoInterConsulta(contexto) {
    contexto= contexto || {};
    var selectPlantilla= document.getElementById("selectPlantillaSeguimientoInterConsulta");
    var selectResponsable= document.getElementById("responsableSeguimientoInterConsulta");
    var valorPlantilla= selectPlantilla ? selectPlantilla.value : "";
    var valorResponsable= selectResponsable ? selectResponsable.value : "";
    plantillasSeguimientoInterConsulta= Array.isArray(contexto.plantillas) ? contexto.plantillas : [];
    responsablesSeguimientoInterConsulta= Array.isArray(contexto.responsables) ? contexto.responsables : [];
    plantillasAdministracionSeguimientoInterConsulta= Array.isArray(contexto.plantillas_administracion) ? contexto.plantillas_administracion : [];

    if (selectPlantilla) {
        selectPlantilla.innerHTML= '<option value="">Seguimiento personalizado</option>';
        plantillasSeguimientoInterConsulta.forEach(function(plantilla) {
            var opcion= document.createElement("option");
            opcion.value= plantilla.id_plantilla;
            opcion.textContent= (plantilla.categoria ? plantilla.categoria + " - " : "") + plantilla.nombre;
            selectPlantilla.appendChild(opcion);
        });
        selectPlantilla.value= valorPlantilla;
        if (selectPlantilla.value != valorPlantilla) {
            selectPlantilla.value= "";
        }
    }
    if (selectResponsable) {
        selectResponsable.innerHTML= "";
        responsablesSeguimientoInterConsulta.forEach(function(responsable) {
            var opcion= document.createElement("option");
            opcion.value= responsable.cod_usuario;
            opcion.textContent= responsable.nombre_persona || ("Usuario " + responsable.cod_usuario);
            selectResponsable.appendChild(opcion);
        });
        selectResponsable.value= valorResponsable || String(userid || "");
        if (!selectResponsable.value && selectResponsable.options.length) {
            selectResponsable.selectedIndex= 0;
        }
    }

    var panelSeguimiento= document.getElementById("panelProgramarSeguimientoInterConsulta");
    if (panelSeguimiento) {
        panelSeguimiento.setAttribute("data-contexto-hilo", String(cod_interConsulta || ""));
    }
    var botonProgramar= document.getElementById("btnProgramarSeguimientoInterConsulta");
    if (botonProgramar) {
        botonProgramar.disabled= false;
        botonProgramar.removeAttribute("title");
    }
    if (selectPlantilla) { selectPlantilla.disabled= false; }
    if (selectResponsable) { selectResponsable.disabled= false; }

    var botonAdministrar= document.getElementById("btnAdministrarPlantillasSeguimientoInterConsulta");
    if (botonAdministrar) {
        botonAdministrar.hidden= Number(contexto.puede_administrar_plantillas || 0) !== 1;
    }
    renderizarAdministracionPlantillasSeguimientoInterConsulta();
    actualizarResumenSeguimientoProgramadoInterConsulta();
}

function cargarContextoSeguimientoProgramadoInterConsulta() {
    if (!cod_interConsulta) {
        return;
    }
    if (solicitudContextoSeguimientoInterConsultaActiva && solicitudContextoSeguimientoInterConsultaActiva.readyState !== 4) {
        solicitudContextoSeguimientoInterConsultaActiva.abort();
    }
    var hiloSolicitado= String(cod_interConsulta);
    var panelSeguimiento= document.getElementById("panelProgramarSeguimientoInterConsulta");
    var selectPlantilla= document.getElementById("selectPlantillaSeguimientoInterConsulta");
    var selectResponsable= document.getElementById("responsableSeguimientoInterConsulta");
    var botonProgramar= document.getElementById("btnProgramarSeguimientoInterConsulta");
    if (!panelSeguimiento || panelSeguimiento.getAttribute("data-contexto-hilo") !== hiloSolicitado) {
        plantillasSeguimientoInterConsulta= [];
        plantillasAdministracionSeguimientoInterConsulta= [];
        responsablesSeguimientoInterConsulta= [];
        if (selectPlantilla) {
            selectPlantilla.innerHTML= '<option value="">Cargando plantillas...</option>';
            selectPlantilla.disabled= true;
        }
        if (selectResponsable) {
            selectResponsable.innerHTML= '<option value="">Cargando responsables...</option>';
            selectResponsable.disabled= true;
        }
    }
    if (botonProgramar) { botonProgramar.disabled= true; }
    obtener_datos_user();
    var datos= new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "obtenerContextoSeguimientoProgramado");
    datos.append("cod_interConsulta", cod_interConsulta);
    var solicitud= $.ajax({
        data: datos,
        url: "../php_system/abmInterConsulta.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        success: function(responseText) {
            if (String(cod_interConsulta) !== hiloSolicitado) {
                return;
            }
            try {
                var respuesta= $.parseJSON(responseText);
                if (respuesta["1"] == "exito") {
                    renderizarOpcionesSeguimientoInterConsulta(respuesta["2"] || {});
                } else if (botonProgramar) {
                    botonProgramar.disabled= true;
                    botonProgramar.title= mensajeRespuestaSeguimientoInterConsulta(respuesta, "No se pudo preparar el seguimiento.");
                }
            } catch (error) {
                console.warn("No se pudo preparar el seguimiento programado.", error);
            }
        },
        error: function(jqXHR, textstatus) {
            if (textstatus != "abort" && String(cod_interConsulta) === hiloSolicitado && botonProgramar) {
                botonProgramar.disabled= true;
                botonProgramar.title= "No se pudo cargar la configuración. Vuelva a intentar.";
            }
        },
        complete: function() {
            if (solicitudContextoSeguimientoInterConsultaActiva === solicitud) {
                solicitudContextoSeguimientoInterConsultaActiva= null;
            }
        }
    });
    solicitudContextoSeguimientoInterConsultaActiva= solicitud;
}

function reiniciarFormularioSeguimientoInterConsulta() {
    var fecha= document.getElementById("fechaProgramadaSeguimientoInterConsulta");
    var plantilla= document.getElementById("selectPlantillaSeguimientoInterConsulta");
    var motivo= document.getElementById("motivoSeguimientoInterConsulta");
    var mensaje= document.getElementById("mensajeSeguimientoInterConsulta");
    var responsable= document.getElementById("responsableSeguimientoInterConsulta");
    var origen= document.getElementById("idSeguimientoOrigenInterConsulta");
    var token= document.getElementById("tokenSolicitudSeguimientoInterConsulta");
    if (fecha) { fecha.value= fechaLocalInputSeguimientoInterConsulta(fechaRapidaSeguimientoInterConsulta(1, 0)); }
    if (plantilla) { plantilla.value= ""; }
    if (motivo) { motivo.value= ""; }
    if (mensaje) { mensaje.value= ""; }
    if (responsable) {
        responsable.value= String(userid || "");
        if (!responsable.value && responsable.options.length) { responsable.selectedIndex= 0; }
    }
    if (origen) { origen.value= ""; }
    if (token) { token.value= generarTokenSeguimientoInterConsulta(); }
    var titulo= document.getElementById("tituloProgramarSeguimientoInterConsulta");
    if (titulo) { titulo.textContent= "Programar seguimiento"; }
    var boton= document.getElementById("btnProgramarSeguimientoInterConsulta");
    if (boton) { boton.innerHTML= '<i class="fa-solid fa-calendar-check" aria-hidden="true"></i> Programar seguimiento'; }
    document.querySelectorAll("#panelProgramarSeguimientoInterConsulta .interconsulta-followup-quick-dates button").forEach(function(opcion) {
        var seleccionada= opcion.getAttribute("data-followup-days") == "1";
        opcion.classList.toggle("is-active", seleccionada);
        opcion.setAttribute("aria-pressed", seleccionada ? "true" : "false");
    });
    actualizarResumenSeguimientoProgramadoInterConsulta();
}

function obtenerControlesEnfocablesSeguimientoInterConsulta() {
    var panel= document.getElementById("panelProgramarSeguimientoInterConsulta");
    if (!panel || panel.hidden) { return []; }
    var selector= 'button:not([disabled]), a[href], input:not([type="hidden"]):not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
    return Array.prototype.filter.call(panel.querySelectorAll(selector), function(elemento) {
        return !elemento.closest("[hidden]") && (elemento.offsetWidth || elemento.offsetHeight || elemento.getClientRects().length);
    });
}

function enfocarElementoSeguimientoInterConsulta(elemento) {
    if (!elemento || typeof elemento.focus != "function") { return; }
    try {
        elemento.focus({preventScroll: true});
    } catch (error) {
        elemento.focus();
    }
}

function enfocarTituloDialogoSeguimientoInterConsulta() {
    var panel= document.getElementById("panelProgramarSeguimientoInterConsulta");
    if (!panel || panel.hidden) { return; }
    var admin= document.getElementById("panelAdministrarPlantillasSeguimientoInterConsulta");
    var idTitulo= admin && !admin.hidden ? "tituloAdministrarPlantillasSeguimientoInterConsulta" : "tituloProgramarSeguimientoInterConsulta";
    var titulo= document.getElementById(idTitulo);
    var ejecutar= function() {
        if (!panel.hidden) { enfocarElementoSeguimientoInterConsulta(titulo); }
    };
    ejecutar();
    if (typeof window.requestAnimationFrame == "function") {
        window.requestAnimationFrame(ejecutar);
    } else {
        setTimeout(ejecutar, 0);
    }
}

function mostrarVistaFormularioSeguimientoInterConsulta(enfocarControl) {
    var dialogo= document.getElementById("panelProgramarSeguimientoInterConsulta");
    var vista= document.getElementById("vistaFormularioSeguimientoInterConsulta");
    var admin= document.getElementById("panelAdministrarPlantillasSeguimientoInterConsulta");
    var accionesFormulario= document.getElementById("accionesProgramarSeguimientoInterConsulta");
    var accionesAdmin= document.getElementById("accionesAdministrarPlantillasSeguimientoInterConsulta");
    var botonAdministrar= document.getElementById("btnAdministrarPlantillasSeguimientoInterConsulta");
    if (vista) { vista.hidden= false; }
    if (admin) { admin.hidden= true; }
    if (accionesFormulario) { accionesFormulario.hidden= false; }
    if (accionesAdmin) { accionesAdmin.hidden= true; }
    if (dialogo) {
        dialogo.setAttribute("aria-labelledby", "tituloProgramarSeguimientoInterConsulta");
        dialogo.setAttribute("aria-describedby", "descripcionProgramarSeguimientoInterConsulta");
    }
    if (botonAdministrar) { botonAdministrar.setAttribute("aria-expanded", "false"); }
    if (enfocarControl) {
        if (botonAdministrar && !botonAdministrar.hidden) {
            enfocarElementoSeguimientoInterConsulta(botonAdministrar);
        } else {
            enfocarTituloDialogoSeguimientoInterConsulta();
        }
    }
}

function manejarTecladoDialogoSeguimientoInterConsulta(event) {
    var panel= document.getElementById("panelProgramarSeguimientoInterConsulta");
    if (!panel || panel.hidden || !event) { return; }
    if (event.key == "Escape" || event.keyCode == 27) {
        event.preventDefault();
        var admin= document.getElementById("panelAdministrarPlantillasSeguimientoInterConsulta");
        if (admin && !admin.hidden) {
            mostrarVistaFormularioSeguimientoInterConsulta(true);
        } else {
            cerrarPanelSeguimientoProgramadoInterConsulta();
        }
        return;
    }
    if (event.key != "Tab" && event.keyCode != 9) { return; }
    var controles= obtenerControlesEnfocablesSeguimientoInterConsulta();
    if (!controles.length) {
        event.preventDefault();
        enfocarTituloDialogoSeguimientoInterConsulta();
        return;
    }
    var primero= controles[0];
    var ultimo= controles[controles.length - 1];
    var indiceActivo= controles.indexOf(document.activeElement);
    if (indiceActivo < 0) {
        event.preventDefault();
        enfocarElementoSeguimientoInterConsulta(event.shiftKey ? ultimo : primero);
    } else if (event.shiftKey && document.activeElement === primero) {
        event.preventDefault();
        enfocarElementoSeguimientoInterConsulta(ultimo);
    } else if (!event.shiftKey && document.activeElement === ultimo) {
        event.preventDefault();
        enfocarElementoSeguimientoInterConsulta(primero);
    }
}

function manejarClickFondoSeguimientoInterConsulta(event) {
    if (event && event.target === event.currentTarget) {
        enfocarTituloDialogoSeguimientoInterConsulta();
    }
}

function inicializarDialogoSeguimientoInterConsulta() {
    var panel= document.getElementById("panelProgramarSeguimientoInterConsulta");
    if (!panel || manejadorDialogoSeguimientoInterConsultaInicializado) { return; }
    panel.addEventListener("keydown", manejarTecladoDialogoSeguimientoInterConsulta);
    manejadorDialogoSeguimientoInterConsultaInicializado= true;
}

function abrirPanelSeguimientoProgramadoInterConsulta() {
    if (!cod_interConsulta) {
        ver_vetana_informativa("Abra un hilo", "Primero seleccione el hilo donde desea programar el seguimiento.", "advertencia");
        return;
    }
    var panel= document.getElementById("panelProgramarSeguimientoInterConsulta");
    var boton= document.getElementById("btnAbrirSeguimientoProgramadoInterConsulta");
    if (!panel) {
        return;
    }
    var hiloActual= String(cod_interConsulta);
    if (panel.getAttribute("data-cod-hilo") !== hiloActual) {
        reiniciarFormularioSeguimientoInterConsulta();
        panel.setAttribute("data-cod-hilo", hiloActual);
        panel.removeAttribute("data-contexto-hilo");
    }
    var token= document.getElementById("tokenSolicitudSeguimientoInterConsulta");
    if (!token || !token.value) {
        reiniciarFormularioSeguimientoInterConsulta();
    }
    elementoFocoAnteriorSeguimientoInterConsulta= document.activeElement && !panel.contains(document.activeElement) ? document.activeElement : boton;
    mostrarVistaFormularioSeguimientoInterConsulta(false);
    panel.hidden= false;
    panel.setAttribute("aria-hidden", "false");
    document.body.classList.add("interconsulta-followup-dialog-open");
    if (boton) { boton.setAttribute("aria-expanded", "true"); }
    cargarContextoSeguimientoProgramadoInterConsulta();
    actualizarResumenSeguimientoProgramadoInterConsulta();
    enfocarTituloDialogoSeguimientoInterConsulta();
}

function cerrarPanelSeguimientoProgramadoInterConsulta(restaurarFoco) {
    var panel= document.getElementById("panelProgramarSeguimientoInterConsulta");
    var boton= document.getElementById("btnAbrirSeguimientoProgramadoInterConsulta");
    if (restaurarFoco === undefined) { restaurarFoco= true; }
    if (panel) {
        panel.hidden= true;
        panel.setAttribute("aria-hidden", "true");
    }
    document.body.classList.remove("interconsulta-followup-dialog-open");
    if (boton) { boton.setAttribute("aria-expanded", "false"); }
    mostrarVistaFormularioSeguimientoInterConsulta(false);
    var focoAnterior= elementoFocoAnteriorSeguimientoInterConsulta;
    elementoFocoAnteriorSeguimientoInterConsulta= null;
    if (restaurarFoco && focoAnterior && document.documentElement.contains(focoAnterior)) {
        enfocarElementoSeguimientoInterConsulta(focoAnterior);
    }
}

function alternarPanelSeguimientoProgramadoInterConsulta() {
    var panel= document.getElementById("panelProgramarSeguimientoInterConsulta");
    if (!panel || panel.hidden) {
        abrirPanelSeguimientoProgramadoInterConsulta();
    } else {
        cerrarPanelSeguimientoProgramadoInterConsulta();
    }
}

function seleccionarPlantillaSeguimientoInterConsulta() {
    var select= document.getElementById("selectPlantillaSeguimientoInterConsulta");
    var plantilla= select ? obtenerPlantillaSeguimientoInterConsulta(select.value) : null;
    if (plantilla) {
        document.getElementById("motivoSeguimientoInterConsulta").value= plantilla.nombre || "";
        document.getElementById("mensajeSeguimientoInterConsulta").value= plantilla.mensaje || "";
    }
    actualizarResumenSeguimientoProgramadoInterConsulta();
}

function actualizarResumenSeguimientoProgramadoInterConsulta() {
    var campoFecha= document.getElementById("fechaProgramadaSeguimientoInterConsulta");
    var campoMotivo= document.getElementById("motivoSeguimientoInterConsulta");
    var campoMensaje= document.getElementById("mensajeSeguimientoInterConsulta");
    var campoResponsable= document.getElementById("responsableSeguimientoInterConsulta");
    var resumen= document.getElementById("resumenSeguimientoProgramadoInterConsulta");
    var contador= document.getElementById("contadorMensajeSeguimientoInterConsulta");
    if (contador && campoMensaje) {
        contador.textContent= String((campoMensaje.value || "").length);
    }
    if (!resumen) {
        return;
    }
    var fechaTexto= "sin fecha";
    if (campoFecha && campoFecha.value) {
        var fecha= new Date(campoFecha.value);
        if (!isNaN(fecha.getTime())) {
            fechaTexto= fecha.toLocaleString("es-PY", {day: "2-digit", month: "2-digit", year: "numeric", hour: "2-digit", minute: "2-digit"});
        }
    }
    var responsable= campoResponsable && campoResponsable.selectedIndex >= 0 ? campoResponsable.options[campoResponsable.selectedIndex].text : "sin responsable";
    var motivo= campoMotivo && campoMotivo.value.trim() ? campoMotivo.value.trim() : "seguimiento personalizado";
    resumen.textContent= "El " + fechaTexto + ", " + responsable + " recibirá una alerta interna para: " + motivo + ".";
}

function mensajeRespuestaSeguimientoInterConsulta(respuesta, textoPredeterminado) {
    var datos= respuesta && respuesta["2"] ? respuesta["2"] : {};
    return datos.mensaje || respuesta.mensaje || textoPredeterminado;
}

function programarSeguimientoInterConsulta() {
    var fecha= document.getElementById("fechaProgramadaSeguimientoInterConsulta");
    var plantilla= document.getElementById("selectPlantillaSeguimientoInterConsulta");
    var motivo= document.getElementById("motivoSeguimientoInterConsulta");
    var mensaje= document.getElementById("mensajeSeguimientoInterConsulta");
    var responsable= document.getElementById("responsableSeguimientoInterConsulta");
    var origen= document.getElementById("idSeguimientoOrigenInterConsulta");
    var token= document.getElementById("tokenSolicitudSeguimientoInterConsulta");
    var panel= document.getElementById("panelProgramarSeguimientoInterConsulta");
    var hiloSolicitado= String(cod_interConsulta || "");
    if (!hiloSolicitado || !panel || panel.getAttribute("data-cod-hilo") !== hiloSolicitado) {
        ver_vetana_informativa("El hilo cambió", "Vuelva a abrir Seguimiento programado para confirmar los datos del hilo actual.", "advertencia");
        reiniciarFormularioSeguimientoInterConsulta();
        cerrarPanelSeguimientoProgramadoInterConsulta();
        return;
    }
    if (!fecha || !fecha.value || isNaN(new Date(fecha.value).getTime()) || new Date(fecha.value).getTime() <= Date.now()) {
        ver_vetana_informativa("Revise la fecha", "El seguimiento debe tener una fecha y hora futuras.", "advertencia");
        if (fecha) { fecha.focus(); }
        return;
    }
    if (!motivo || !motivo.value.trim()) {
        ver_vetana_informativa("Falta el motivo", "Seleccione una plantilla o escriba el motivo del seguimiento.", "advertencia");
        if (motivo) { motivo.focus(); }
        return;
    }
    if (!mensaje || !mensaje.value.trim()) {
        ver_vetana_informativa("Falta la nota", "Escriba qué debe realizarse durante el seguimiento.", "advertencia");
        if (mensaje) { mensaje.focus(); }
        return;
    }
    if (!responsable || !responsable.value) {
        ver_vetana_informativa("Falta el responsable", "Seleccione quién recibirá la alerta interna.", "advertencia");
        return;
    }
    if (!token.value) { token.value= generarTokenSeguimientoInterConsulta(); }
    obtener_datos_user();
    var datos= new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "programarSeguimientoInterConsulta");
    datos.append("cod_interConsulta", hiloSolicitado);
    datos.append("id_plantilla", plantilla ? plantilla.value : "");
    datos.append("motivo", motivo.value.trim());
    datos.append("mensaje", mensaje.value.trim());
    datos.append("fecha_programada", fecha.value);
    datos.append("cod_responsable", responsable.value);
    datos.append("id_seguimiento_origen", origen ? origen.value : "");
    datos.append("token_solicitud", token.value);
    var boton= document.getElementById("btnProgramarSeguimientoInterConsulta");
    if (boton) { boton.disabled= true; }
    $.ajax({
        data: datos,
        url: "../php_system/abmInterConsulta.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        error: function(jqXHR, textstatus) {
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
            ver_vetana_informativa("No se pudo programar", "Intente nuevamente sin cerrar el formulario.", "error");
        },
        success: function(responseText) {
            try {
                var respuesta= $.parseJSON(responseText);
                if (respuesta["1"] == "exito") {
                    ver_vetana_informativa("Seguimiento programado", "La tarea quedó registrada en el timeline y en las alertas internas.", "info");
                    if (String(cod_interConsulta) === hiloSolicitado) {
                        reiniciarFormularioSeguimientoInterConsulta();
                        cerrarPanelSeguimientoProgramadoInterConsulta();
                        buscarInterConsultasYContenido(hiloSolicitado);
                    }
                    buscarPacientesConInterConsultas();
                    cargarAlertasSeguimientoInterConsulta();
                } else {
                    ver_vetana_informativa("No se pudo programar", mensajeRespuestaSeguimientoInterConsulta(respuesta, "Revise los datos e intente nuevamente."), "advertencia");
                }
            } catch (error) {
                ver_vetana_informativa("No se pudo programar", "La respuesta del servidor no fue válida.", "error");
                GuardarArchivosLog("Error al programar seguimiento: " + error + " \r\n Consola: " + responseText);
            }
        },
        complete: function() {
            if (boton && String(cod_interConsulta) === hiloSolicitado
                && panel && panel.getAttribute("data-cod-hilo") === hiloSolicitado) {
                boton.disabled= false;
            }
        }
    });
}

function renderizarAdministracionPlantillasSeguimientoInterConsulta() {
    var select= document.getElementById("selectAdministrarPlantillaSeguimientoInterConsulta");
    if (!select) {
        return;
    }
    var valor= select.value;
    select.innerHTML= '<option value="">Seleccione una plantilla</option>';
    plantillasAdministracionSeguimientoInterConsulta.forEach(function(plantilla) {
        var opcion= document.createElement("option");
        opcion.value= plantilla.id_plantilla;
        opcion.textContent= plantilla.nombre + (plantilla.estado == "inactivo" ? " (inactiva)" : "");
        select.appendChild(opcion);
    });
    select.value= valor;
    if (select.value != valor) { select.value= ""; }
}

function alternarAdministracionPlantillasSeguimientoInterConsulta() {
    var panel= document.getElementById("panelAdministrarPlantillasSeguimientoInterConsulta");
    if (!panel) { return; }
    if (!panel.hidden) {
        mostrarVistaFormularioSeguimientoInterConsulta(true);
        return;
    }
    var vista= document.getElementById("vistaFormularioSeguimientoInterConsulta");
    var dialogo= document.getElementById("panelProgramarSeguimientoInterConsulta");
    var accionesFormulario= document.getElementById("accionesProgramarSeguimientoInterConsulta");
    var accionesAdmin= document.getElementById("accionesAdministrarPlantillasSeguimientoInterConsulta");
    var botonAdministrar= document.getElementById("btnAdministrarPlantillasSeguimientoInterConsulta");
    if (vista) { vista.hidden= true; }
    panel.hidden= false;
    if (accionesFormulario) { accionesFormulario.hidden= true; }
    if (accionesAdmin) { accionesAdmin.hidden= false; }
    if (dialogo) {
        dialogo.setAttribute("aria-labelledby", "tituloAdministrarPlantillasSeguimientoInterConsulta");
        dialogo.setAttribute("aria-describedby", "descripcionAdministrarPlantillasSeguimientoInterConsulta");
    }
    if (botonAdministrar) { botonAdministrar.setAttribute("aria-expanded", "true"); }
    renderizarAdministracionPlantillasSeguimientoInterConsulta();
    enfocarTituloDialogoSeguimientoInterConsulta();
}

function cargarPlantillaAdministracionSeguimientoInterConsulta() {
    var select= document.getElementById("selectAdministrarPlantillaSeguimientoInterConsulta");
    var plantilla= select ? obtenerPlantillaSeguimientoInterConsulta(select.value, plantillasAdministracionSeguimientoInterConsulta) : null;
    if (!plantilla) {
        nuevaPlantillaSeguimientoInterConsulta(false);
        return;
    }
    document.getElementById("idPlantillaAdministracionSeguimientoInterConsulta").value= plantilla.id_plantilla || "";
    document.getElementById("nombrePlantillaAdministracionSeguimientoInterConsulta").value= plantilla.nombre || "";
    document.getElementById("categoriaPlantillaAdministracionSeguimientoInterConsulta").value= plantilla.categoria || "";
    document.getElementById("ordenPlantillaAdministracionSeguimientoInterConsulta").value= plantilla.orden || "0";
    document.getElementById("estadoPlantillaAdministracionSeguimientoInterConsulta").value= plantilla.estado || "activo";
    document.getElementById("mensajePlantillaAdministracionSeguimientoInterConsulta").value= plantilla.mensaje || "";
}

function nuevaPlantillaSeguimientoInterConsulta(enfocar) {
    var select= document.getElementById("selectAdministrarPlantillaSeguimientoInterConsulta");
    if (select) { select.value= ""; }
    document.getElementById("idPlantillaAdministracionSeguimientoInterConsulta").value= "";
    document.getElementById("nombrePlantillaAdministracionSeguimientoInterConsulta").value= "";
    document.getElementById("categoriaPlantillaAdministracionSeguimientoInterConsulta").value= "";
    document.getElementById("ordenPlantillaAdministracionSeguimientoInterConsulta").value= "0";
    document.getElementById("estadoPlantillaAdministracionSeguimientoInterConsulta").value= "activo";
    document.getElementById("mensajePlantillaAdministracionSeguimientoInterConsulta").value= "";
    if (enfocar !== false) { document.getElementById("nombrePlantillaAdministracionSeguimientoInterConsulta").focus(); }
}

function guardarPlantillaSeguimientoInterConsulta() {
    var id= document.getElementById("idPlantillaAdministracionSeguimientoInterConsulta").value;
    var nombre= document.getElementById("nombrePlantillaAdministracionSeguimientoInterConsulta").value.trim();
    var categoria= document.getElementById("categoriaPlantillaAdministracionSeguimientoInterConsulta").value.trim();
    var orden= document.getElementById("ordenPlantillaAdministracionSeguimientoInterConsulta").value;
    var estado= document.getElementById("estadoPlantillaAdministracionSeguimientoInterConsulta").value;
    var mensaje= document.getElementById("mensajePlantillaAdministracionSeguimientoInterConsulta").value.trim();
    if (!nombre || !mensaje) {
        ver_vetana_informativa("Faltan datos", "La plantilla necesita un nombre y un mensaje sugerido.", "advertencia");
        return;
    }
    obtener_datos_user();
    var datos= new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "guardarPlantillaSeguimientoProgramado");
    datos.append("id_plantilla", id);
    datos.append("nombre", nombre);
    datos.append("categoria", categoria);
    datos.append("orden", orden || "0");
    datos.append("estado", estado);
    datos.append("mensaje", mensaje);
    var botonGuardar= document.getElementById("btnGuardarPlantillaSeguimientoInterConsulta");
    if (botonGuardar) { botonGuardar.disabled= true; }
    $.ajax({
        data: datos,
        url: "../php_system/abmInterConsulta.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        error: function() {
            ver_vetana_informativa("No se pudo guardar", "Revise la conexión e intente nuevamente. Los datos escritos se conservaron.", "error");
        },
        success: function(responseText) {
            try {
                var respuesta= $.parseJSON(responseText);
                if (respuesta["1"] == "exito") {
                    ver_vetana_informativa("Plantilla guardada", "Ya está disponible para los seguimientos.", "info");
                    cargarContextoSeguimientoProgramadoInterConsulta();
                } else {
                    ver_vetana_informativa("No se pudo guardar", mensajeRespuestaSeguimientoInterConsulta(respuesta, "Revise los datos."), "advertencia");
                }
            } catch (error) {
                ver_vetana_informativa("No se pudo guardar", "La respuesta del servidor no fue válida.", "error");
            }
        },
        complete: function() {
            if (botonGuardar) { botonGuardar.disabled= false; }
        }
    });
}

function datosSeguimientoDesdeTarjetaInterConsulta(tarjeta) {
    return {
        id: tarjeta ? tarjeta.getAttribute("data-seguimiento-id") || "" : "",
        id_plantilla: tarjeta ? tarjeta.getAttribute("data-plantilla-id") || "" : "",
        motivo: tarjeta ? tarjeta.getAttribute("data-motivo") || "" : "",
        mensaje: tarjeta ? tarjeta.getAttribute("data-mensaje") || "" : "",
        fecha: tarjeta ? tarjeta.getAttribute("data-fecha-programada") || "" : "",
        responsable: tarjeta ? tarjeta.getAttribute("data-responsable") || "" : ""
    };
}

function prepararSiguienteSeguimientoInterConsulta(datosTarjeta, esReprogramacion) {
    if (!cod_interConsulta) { return; }
    reiniciarFormularioSeguimientoInterConsulta();
    abrirPanelSeguimientoProgramadoInterConsulta();
    var panel= document.getElementById("panelProgramarSeguimientoInterConsulta");
    if (panel) { panel.setAttribute("data-cod-hilo", String(cod_interConsulta || "")); }
    var datos= datosTarjeta || {};
    document.getElementById("idSeguimientoOrigenInterConsulta").value= esReprogramacion ? (datos.id || "") : "";
    document.getElementById("selectPlantillaSeguimientoInterConsulta").value= datos.id_plantilla || "";
    document.getElementById("motivoSeguimientoInterConsulta").value= datos.motivo || "";
    document.getElementById("mensajeSeguimientoInterConsulta").value= datos.mensaje || "";
    document.getElementById("responsableSeguimientoInterConsulta").value= datos.responsable || String(userid || "");
    var boton= document.getElementById("btnProgramarSeguimientoInterConsulta");
    var titulo= document.getElementById("tituloProgramarSeguimientoInterConsulta");
    if (boton && esReprogramacion) {
        boton.innerHTML= '<i class="fa-solid fa-calendar-plus" aria-hidden="true"></i> Reprogramar seguimiento';
    }
    if (titulo && esReprogramacion) {
        titulo.textContent= "Reprogramar seguimiento";
    }
    actualizarResumenSeguimientoProgramadoInterConsulta();
    enfocarTituloDialogoSeguimientoInterConsulta();
}

function completarSeguimientoInterConsulta(tarjeta, programarOtro) {
    if (!tarjeta) { return; }
    var resultado= tarjeta.querySelector('[data-role="resultado-seguimiento"]');
    if (!resultado || !resultado.value.trim()) {
        ver_vetana_informativa("Falta el resultado", "Indique qué ocurrió durante la gestión.", "advertencia");
        if (resultado) { resultado.focus(); }
        return;
    }
    var copia= datosSeguimientoDesdeTarjetaInterConsulta(tarjeta);
    var hiloSolicitado= String(tarjeta.getAttribute("data-cod-hilo") || cod_interConsulta || "");
    if (!hiloSolicitado || String(cod_interConsulta) !== hiloSolicitado) {
        ver_vetana_informativa("El hilo cambió", "Abra nuevamente el seguimiento antes de completarlo.", "advertencia");
        return;
    }
    obtener_datos_user();
    var datos= new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "completarSeguimientoInterConsulta");
    datos.append("id_seguimiento", copia.id);
    datos.append("resultado", resultado.value.trim());
    tarjeta.querySelectorAll("button").forEach(function(boton) { boton.disabled= true; });
    $.ajax({
        data: datos,
        url: "../php_system/abmInterConsulta.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        error: function() {
            ver_vetana_informativa("No se pudo completar", "Intente nuevamente.", "error");
        },
        success: function(responseText) {
            try {
                var respuesta= $.parseJSON(responseText);
                if (respuesta["1"] == "exito") {
                    ver_vetana_informativa("Seguimiento completado", "El resultado quedó registrado en el timeline.", "info");
                    if (String(cod_interConsulta) === hiloSolicitado) {
                        if (programarOtro) {
                            buscarInterConsultasYContenido(hiloSolicitado, null, function() {
                                prepararSiguienteSeguimientoInterConsulta(copia, false);
                            });
                        } else {
                            buscarInterConsultasYContenido(hiloSolicitado);
                        }
                    }
                    buscarPacientesConInterConsultas();
                    cargarAlertasSeguimientoInterConsulta();
                } else {
                    ver_vetana_informativa("No se pudo completar", mensajeRespuestaSeguimientoInterConsulta(respuesta, "El seguimiento pudo haber sido atendido por otro usuario."), "advertencia");
                }
            } catch (error) {
                ver_vetana_informativa("No se pudo completar", "La respuesta del servidor no fue válida.", "error");
            }
        },
        complete: function() {
            tarjeta.querySelectorAll("button").forEach(function(boton) { boton.disabled= false; });
        }
    });
}

function cancelarSolicitudContextoMensajeInterConsulta() {
    secuenciaContextoMensajeInterConsulta++;
    if (solicitudContextoMensajeInterConsultaActiva && solicitudContextoMensajeInterConsultaActiva.readyState !== 4) {
        solicitudContextoMensajeInterConsultaActiva.abort();
    }
    solicitudContextoMensajeInterConsultaActiva= null;
}

function solicitarContextoMensajeInterConsulta(codMensaje, callback) {
    cancelarSolicitudContextoMensajeInterConsulta();
    var hiloSolicitado= String(cod_interConsulta || "");
    var secuenciaSolicitud= ++secuenciaContextoMensajeInterConsulta;
    obtener_datos_user();
    var datos= new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "buscarContextoMensajeInterConsulta");
    datos.append("cod_interConsulta", hiloSolicitado);
    datos.append("cod_mensaje", codMensaje);
    var solicitud= $.ajax({
        data: datos,
        url: "../php_system/abmInterConsulta.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        success: function(responseText) {
            if (secuenciaSolicitud !== secuenciaContextoMensajeInterConsulta || String(cod_interConsulta) !== hiloSolicitado) {
                return;
            }
            try {
                var respuesta= $.parseJSON(responseText);
                if (respuesta["1"] == "exito") {
                    callback(null, respuesta["2"] || {});
                } else {
                    callback(new Error(mensajeRespuestaSeguimientoInterConsulta(respuesta, "El mensaje original ya no está disponible.")));
                }
            } catch (error) {
                callback(error);
            }
        },
        error: function(jqXHR, textstatus) {
            if (textstatus != "abort" && secuenciaSolicitud === secuenciaContextoMensajeInterConsulta && String(cod_interConsulta) === hiloSolicitado) {
                callback(new Error("No se pudo consultar el mensaje original."));
            }
        },
        complete: function() {
            if (solicitudContextoMensajeInterConsultaActiva === solicitud) {
                solicitudContextoMensajeInterConsultaActiva= null;
            }
        }
    });
    solicitudContextoMensajeInterConsultaActiva= solicitud;
}

function seleccionarRespuestaCitadaInterConsulta(codMensaje) {
    if (!codMensaje) { return; }
    solicitarContextoMensajeInterConsulta(codMensaje, function(error, mensaje) {
        if (error) {
            ver_vetana_informativa("No se puede citar", error.message, "advertencia");
            return;
        }
        var contenido= String(mensaje.contenido || "").replace(/@\{\d+\}/g, "@usuario").replace(/\s+/g, " ").trim();
        if (contenido.length > 180) { contenido= contenido.slice(0, 177) + "..."; }
        document.getElementById("codMensajeRespuestaInterConsulta").value= mensaje.cod_mensaje || codMensaje;
        document.getElementById("autorRespuestaCitadaInterConsulta").textContent= mensaje.nombre_persona || "Participante del hilo";
        document.getElementById("fechaRespuestaCitadaInterConsulta").textContent= mensaje.fecha_creacion || "";
        document.getElementById("textoRespuestaCitadaInterConsulta").textContent= contenido || "Mensaje sin texto";
        document.getElementById("vistaRespuestaCitadaInterConsulta").hidden= false;
        var editor= document.getElementById("inptContenidoAbmMensaje");
        if (editor) { editor.focus(); }
    });
}

function cancelarRespuestaCitadaInterConsulta() {
    var codigo= document.getElementById("codMensajeRespuestaInterConsulta");
    var vista= document.getElementById("vistaRespuestaCitadaInterConsulta");
    if (codigo) { codigo.value= ""; }
    if (vista) { vista.hidden= true; }
}

function irMensajeCitadoInterConsulta(codMensaje) {
    var mensaje= document.getElementById("mensajeInterConsulta-" + codMensaje);
    if (mensaje) {
        mensaje.classList.add("interconsulta-message-row--highlight");
        mensaje.scrollIntoView({behavior: "smooth", block: "center"});
        setTimeout(function() { mensaje.classList.remove("interconsulta-message-row--highlight"); }, 2200);
        return;
    }
    solicitarContextoMensajeInterConsulta(codMensaje, function(error, contexto) {
        if (error) {
            ver_vetana_informativa("Mensaje no disponible", error.message, "advertencia");
            return;
        }
        var texto= escaparHtmlSeguimientoHilo(String(contexto.contenido || "").replace(/@\{\d+\}/g, "@usuario")).replace(/\n/g, "<br>");
        var autor= escaparHtmlSeguimientoHilo(contexto.nombre_persona || "un participante");
        ver_vetana_informativa("Mensaje original de " + autor, texto, "info");
    });
}

function manejarAccionTimelineInterConsulta(event) {
    var accion= event.target.closest ? event.target.closest("[data-action]") : null;
    if (!accion) { return; }
    var tipo= accion.getAttribute("data-action");
    var tarjeta= accion.closest(".interconsulta-followup-card");
    if (tipo == "responder-mensaje") {
        seleccionarRespuestaCitadaInterConsulta(accion.getAttribute("data-cod-mensaje"));
    } else if (tipo == "ir-mensaje-citado") {
        irMensajeCitadoInterConsulta(accion.getAttribute("data-cod-mensaje"));
    } else if (tipo == "mostrar-completar-seguimiento" && tarjeta) {
        var panel= tarjeta.querySelector(".interconsulta-followup-complete");
        if (panel) {
            panel.hidden= false;
            accion.setAttribute("aria-expanded", "true");
            var resultado= panel.querySelector('[data-role="resultado-seguimiento"]');
            if (resultado) { resultado.focus(); }
        }
    } else if (tipo == "cancelar-completar-seguimiento" && tarjeta) {
        var panelCancelar= tarjeta.querySelector(".interconsulta-followup-complete");
        if (panelCancelar) { panelCancelar.hidden= true; }
        var botonAbrirCompletar= tarjeta.querySelector('[data-action="mostrar-completar-seguimiento"]');
        if (botonAbrirCompletar) {
            botonAbrirCompletar.setAttribute("aria-expanded", "false");
            botonAbrirCompletar.focus();
        }
    } else if (tipo == "completar-seguimiento") {
        completarSeguimientoInterConsulta(tarjeta, false);
    } else if (tipo == "completar-y-programar-seguimiento") {
        completarSeguimientoInterConsulta(tarjeta, true);
    } else if (tipo == "reprogramar-seguimiento" && tarjeta) {
        prepararSiguienteSeguimientoInterConsulta(datosSeguimientoDesdeTarjetaInterConsulta(tarjeta), true);
    } else {
        return;
    }
    event.preventDefault();
    event.stopPropagation();
}

function actualizarBadgeSeguimientoDetalleDesdeTimeline() {
    var badge= document.getElementById("badgeSeguimientoDetalle");
    var timeline= document.getElementById("table_abm_InterConsulta");
    if (!badge || !timeline) { return; }
    var tarjeta= timeline.querySelector(".interconsulta-followup-card--vencido")
        || timeline.querySelector(".interconsulta-followup-card--para_hoy")
        || timeline.querySelector(".interconsulta-followup-card--programado");
    if (!tarjeta) {
        badge.style.display= "none";
        badge.removeAttribute("data-followup-state");
        return;
    }
    var estado= tarjeta.classList.contains("interconsulta-followup-card--vencido") ? "vencido"
        : (tarjeta.classList.contains("interconsulta-followup-card--para_hoy") ? "para_hoy" : "programado");
    badge.textContent= estado == "vencido" ? "Seguimiento vencido" : (estado == "para_hoy" ? "Seguimiento hoy" : "Seguimiento programado");
    badge.setAttribute("data-followup-state", estado);
    badge.style.display= "";
}

function enfocarSeguimientoAlertaPendienteInterConsulta() {
    if (!idSeguimientoAlertaPendienteInterConsulta) { return; }
    var tarjeta= document.getElementById("seguimientoInterConsulta-" + idSeguimientoAlertaPendienteInterConsulta);
    if (!tarjeta) { return; }
    idSeguimientoAlertaPendienteInterConsulta= 0;
    tarjeta.classList.add("interconsulta-followup-card--highlight");
    tarjeta.scrollIntoView({behavior: "smooth", block: "center"});
    setTimeout(function() { tarjeta.classList.remove("interconsulta-followup-card--highlight"); }, 2200);
}

function actualizarAlertasSeguimientoInterConsulta(resumen) {
    resumen= resumen || {};
    var lista= Array.isArray(resumen.items) ? resumen.items : [];
    var firma= JSON.stringify({
        hoy: Number(resumen.hoy || 0),
        vencidos: Number(resumen.vencidos || 0),
        items: lista.map(function(item) {
            return [String(item.id_seguimiento || ""), String(item.fecha_programada || ""), String(item.motivo || "")];
        })
    });
    if (firma === firmaAlertasSeguimientoInterConsulta) { return; }
    firmaAlertasSeguimientoInterConsulta= firma;
    var hoy= document.getElementById("cantidadSeguimientosHoyInterConsulta");
    var vencidos= document.getElementById("cantidadSeguimientosVencidosInterConsulta");
    var items= document.getElementById("itemsAlertasSeguimientoInterConsulta");
    if (hoy) { hoy.textContent= String(Number(resumen.hoy || 0)); }
    if (vencidos) { vencidos.textContent= String(Number(resumen.vencidos || 0)); }
    if (!items) { return; }
    if (!lista.length) {
        items.innerHTML= '<span class="interconsulta-followup-alerts__empty">Sin alertas pendientes</span>';
        return;
    }
    items.innerHTML= lista.map(function(item) {
        var fecha= String(item.fecha_programada || "");
        var fechaObjeto= new Date(fecha.replace(" ", "T"));
        var vencido= !isNaN(fechaObjeto.getTime()) && fechaObjeto.getTime() < Date.now();
        var fechaTexto= !isNaN(fechaObjeto.getTime()) ? fechaObjeto.toLocaleString("es-PY", {day: "2-digit", month: "2-digit", hour: "2-digit", minute: "2-digit"}) : fecha;
        return '<button type="button" class="interconsulta-followup-alert-item '+(vencido ? 'is-overdue' : 'is-today')+'" data-hilo="'+escaparHtmlSeguimientoHilo(item.cod_interConsultaFK || "")+'" data-seguimiento="'+escaparHtmlSeguimientoHilo(item.id_seguimiento || "")+'" onclick="abrirHiloDesdeAlertaSeguimientoInterConsulta(this)" title="Abrir seguimiento">'
            +'<strong>#'+escaparHtmlSeguimientoHilo(item.cod_interConsultaFK || "")+' '+escaparHtmlSeguimientoHilo(item.motivo || "Seguimiento")+'</strong>'
            +'<span>'+(vencido ? 'Vencido' : 'Hoy')+' &middot; '+escaparHtmlSeguimientoHilo(fechaTexto)+'</span>'
            +'</button>';
    }).join("");
}

function cargarAlertasSeguimientoInterConsulta() {
    if (document.visibilityState && document.visibilityState !== "visible") { return; }
    var listado= document.getElementById("divListadoInterConsulta");
    var detalle= document.getElementById("divAbmDetallesInterConsulta");
    var listadoVisible= listado && window.getComputedStyle(listado).display !== "none";
    var detalleVisible= detalle && window.getComputedStyle(detalle).display !== "none";
    if (!listadoVisible && !detalleVisible) { return; }
    obtener_datos_user();
    if (!userid) { return; }
    if (solicitudAlertasSeguimientoInterConsultaActiva && solicitudAlertasSeguimientoInterConsultaActiva.readyState !== 4) {
        solicitudAlertasSeguimientoInterConsultaActiva.abort();
    }
    var secuenciaSolicitud= ++secuenciaAlertasSeguimientoInterConsulta;
    var datos= new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "buscarAlertasSeguimientoInterConsulta");
    var solicitud= $.ajax({
        data: datos,
        url: "../php_system/abmInterConsulta.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        success: function(responseText) {
            if (secuenciaSolicitud !== secuenciaAlertasSeguimientoInterConsulta) { return; }
            try {
                var respuesta= $.parseJSON(responseText);
                if (respuesta["1"] == "exito") {
                    actualizarAlertasSeguimientoInterConsulta(respuesta["2"] || {});
                }
            } catch (error) {}
        },
        complete: function() {
            if (solicitudAlertasSeguimientoInterConsultaActiva === solicitud) {
                solicitudAlertasSeguimientoInterConsultaActiva= null;
            }
        }
    });
    solicitudAlertasSeguimientoInterConsultaActiva= solicitud;
}

function abrirHiloDesdeAlertaSeguimientoInterConsulta(boton) {
    var hilo= boton ? boton.getAttribute("data-hilo") : "";
    if (!hilo) { return; }
    idSeguimientoAlertaPendienteInterConsulta= parseInt(boton.getAttribute("data-seguimiento") || "0", 10);
    cod_interConsulta= hilo;
    cod_clienteConsulta= "";
    cod_ventaFKConsulta= "";
    limpiarCamposDetallesInterConsulta();
    limpiarcamposMensaje();
    var detalle= document.getElementById("divAbmDetallesInterConsulta");
    if (!detalle || detalle.style.display == "none") {
        verCerrarVentanaDetalleInterConsulta(true, "divListadoInterConsulta");
    }
    buscarInterConsultasYContenido(hilo);
}

function inicializarSeguimientosProgramadosInterConsulta() {
    inicializarDialogoSeguimientoInterConsulta();
    var timeline= document.getElementById("table_abm_InterConsulta");
    if (timeline && !manejadorTimelineSeguimientoInterConsultaInicializado) {
        timeline.addEventListener("click", manejarAccionTimelineInterConsulta);
        manejadorTimelineSeguimientoInterConsultaInicializado= true;
    }
    if (!temporizadorAlertasSeguimientoInterConsulta) {
        setTimeout(cargarAlertasSeguimientoInterConsulta, 1500);
        temporizadorAlertasSeguimientoInterConsulta= setInterval(cargarAlertasSeguimientoInterConsulta, intervaloAlertasSeguimientoInterConsultaMs);
    }
}

function crearDatosSolicitudListadoInterConsulta(filtros, accion) {
    let datos= new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", accion || "buscarInterConsultas");
    datos.append("cod_interConsulta", filtros.cod_interC || "");
    datos.append("cod_usuarioFK", filtros.cod_usuarioFK || "");
    datos.append("asunto", filtros.asunto || "");
    datos.append("nombre_responsable", filtros.nombre_responsable || "");
    datos.append("nombre_cliente", filtros.nombre_cliente || "");
    datos.append("estado", filtros.estado || "");
    datos.append("tipo", filtros.tipo || "");
    datos.append("usuario_vinculado", filtros.usuario_vinculado || "");
    datos.append("cod_localFK", filtros.cod_localFK || "");
    datos.append("busqueda_global", filtros.busqueda_global || "");
    datos.append("fecha_desde", filtros.fecha_desde || "");
    datos.append("fecha_hasta", filtros.fecha_hasta || "");
    datos.append("categoria_principal", filtros.categoria_principal || obtenerCategoriaActivaInterConsulta());
    datos.append("limite", filtros.limite !== undefined ? filtros.limite : limiteMaximoListadoInterConsulta);
    if (Array.isArray(filtros.codigos_hilos) && filtros.codigos_hilos.length) {
        datos.append("codigos_hilos", filtros.codigos_hilos.join(","));
    }
    if (filtros.ocultar_inactivos) {
        datos.append("ocultar_inactivos", filtros.ocultar_inactivos);
    }
    return datos;
}

function abortarSolicitudHilosInterConsulta(solicitud) {
    if (solicitud && solicitud.readyState !== 4) {
        solicitud.abort();
    }
}

function verificarMensajesPendientesListadoInterConsulta(registros) {
    if (!cod_interConsulta || !Array.isArray(registros)) {
        return;
    }
    registros.forEach(function(valor) {
        if (valor.cod_interConsulta == cod_interConsulta && parseInt(valor.cantMensajes, 10) > totalRegistroMensaje) {
            var aviso= document.getElementById('avisoMensajesPendientesInterConsulta');
            if (aviso) {
                aviso.style.display= "flex";
            }
        }
    });
}

function aplicarPaginaListadoInterConsulta(datos, offsetPagina, esEnriquecido) {
    var listado= document.getElementById('table_frm_VistaInterConsulta');
    var contenedorScroll= document.querySelector("#divListadoInterConsulta .hilos-thread-table-wrap");
    var scrollAnterior= contenedorScroll ? contenedorScroll.scrollTop : 0;
    if (listado) {
        listado.innerHTML= datos["2"];
    }
    if (esEnriquecido && contenedorScroll) {
        contenedorScroll.scrollTop= scrollAnterior;
    } else {
        desplazarListadoHilosAlInicio();
    }

    registrocargadoInterConsulta= Number(datos["4"]) || 0;
    paginaOffsetInterConsulta= Math.max(0, Number(offsetPagina) || 0);
    registroInterConsultaAbierta= Number(datos["7"]) || 0;
    totalregistroinformeInterConsulta= Number(datos["5"]) || 0;
    var proceso= document.getElementById("tbProcessInformeInterConsulta");
    if (proceso) {
        proceso.style.display= "none";
    }
    controldebusquedadInformeInterConsulta= false;

    var cargados= document.getElementById('inptRegistoCargadoInterConsulta');
    var abiertos= document.getElementById('inptRegistoInterConsultaAbierta');
    if (cargados) {
        cargados.value= registrocargadoInterConsulta;
    }
    if (abiertos) {
        abiertos.value= registroInterConsultaAbierta;
    }
    if (esEnriquecido) {
        actualizarTabsCategoriaHilosInterConsulta(datos["9"]);
        actualizarActividadDiariaSeguimientoInterConsulta(datos["10"]);
    }
    actualizarBotonCargarMasHilosInterConsulta();
    verificarMensajesPendientesListadoInterConsulta(datos["3"]);
}

function marcarEnriquecimientoListadoInterConsultaNoDisponible() {
    document.querySelectorAll("#table_frm_VistaInterConsulta .interconsulta-thread-row--loading").forEach(function(fila) {
        fila.classList.remove("interconsulta-thread-row--loading");
        fila.querySelectorAll(".interconsulta-status-pill--muted").forEach(function(indicador) {
            var principal= indicador.querySelector("strong");
            var detalle= indicador.querySelector("span");
            if (principal) {
                principal.textContent= "No disponible";
            }
            if (detalle) {
                detalle.textContent= "Actualice para reintentar";
            }
            indicador.title= "No se pudo completar esta informacion. Actualice el listado para reintentar.";
        });
    });
}

function cargarEnriquecimientoListadoInterConsulta(filtros, token, offsetPagina, codigosHilos) {
    abortarSolicitudHilosInterConsulta(solicitudEnriquecimientoListadoInterConsultaActiva);
    var filtrosEnriquecimiento= Object.assign({}, filtros, {
        codigos_hilos: Array.isArray(codigosHilos) ? codigosHilos : []
    });
    var datosSolicitud= crearDatosSolicitudListadoInterConsulta(filtrosEnriquecimiento, "buscarInterConsultasEnriquecidos");
    var solicitud= $.ajax({
        data: datosSolicitud,
        url: "../php_system/abmInterConsulta.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        error: function(jqXHR, textstatus) {
            if (textstatus != "abort" && token == secuenciaListadoInterConsulta) {
                marcarEnriquecimientoListadoInterConsultaNoDisponible();
                console.warn("No se pudo completar la informacion complementaria del listado de hilos.");
            }
        },
        success: function(responseText) {
            if (token != secuenciaListadoInterConsulta || busquedaInterConsultaCancelada) {
                return;
            }
            try {
                var respuesta= $.parseJSON(responseText);
                if (respuestaJqueryAjax(respuesta["1"]) == true) {
                    aplicarPaginaListadoInterConsulta(respuesta, offsetPagina, true);
                } else {
                    marcarEnriquecimientoListadoInterConsultaNoDisponible();
                }
            } catch (error) {
                marcarEnriquecimientoListadoInterConsultaNoDisponible();
                GuardarArchivosLog("Error al completar el listado de hilos: " + error);
            }
        },
        complete: function() {
            if (solicitudEnriquecimientoListadoInterConsultaActiva === solicitud) {
                solicitudEnriquecimientoListadoInterConsultaActiva= null;
            }
        }
    });
    solicitudEnriquecimientoListadoInterConsultaActiva= solicitud;
}

function buscarPacientesConInterConsultas2(cod_interC, asunto, nombre_responsable, nombre_cliente, estado, tipo, cod_localFK, cod_usuarioFK, limite, ocultar_inactivos, usuario_vinculado, busqueda_global= "", fecha_desde= "", fecha_hasta= "", categoria_principal= "") {
    categoria_principal= categoria_principal || obtenerCategoriaActivaInterConsulta();
    var filtrosSolicitud= {
        cod_interC: cod_interC,
        cod_usuarioFK: cod_usuarioFK,
        asunto: asunto,
        nombre_responsable: nombre_responsable,
        nombre_cliente: nombre_cliente,
        estado: estado,
        tipo: tipo,
        usuario_vinculado: usuario_vinculado,
        cod_localFK: cod_localFK,
        busqueda_global: busqueda_global,
        fecha_desde: fecha_desde,
        fecha_hasta: fecha_hasta,
        categoria_principal: categoria_principal,
        ocultar_inactivos: ocultar_inactivos,
        limite: limite
    };
    var esCargaListado= limite != 0;
    var token= secuenciaListadoInterConsulta;

    if (esCargaListado) {
        token= ++secuenciaListadoInterConsulta;
        abortarSolicitudHilosInterConsulta(solicitudListadoInterConsultaActiva);
        abortarSolicitudHilosInterConsulta(solicitudEnriquecimientoListadoInterConsultaActiva);
        busquedaInterConsultaCancelada= false;
        controldebusquedadInformeInterConsulta= false;
        registrocargadoInterConsulta= 0;
        paginaOffsetInterConsulta= 0;
        registroInterConsultaAbierta= 0;
        totalregistroinformeInterConsulta= 0;
        guardarFiltrosBusquedaInterConsulta(cod_interC, asunto, nombre_responsable, nombre_cliente, estado, tipo, cod_localFK, cod_usuarioFK, ocultar_inactivos, usuario_vinculado, busqueda_global, fecha_desde, fecha_hasta, categoria_principal);
        actualizarBotonCargarMasHilosInterConsulta();
        var etiquetaProceso= document.getElementById("lblProcessInformeInterConsulta");
        if (etiquetaProceso) {
            etiquetaProceso.textContent= "Cargando hilos";
        }
        document.getElementById('table_frm_VistaInterConsulta').innerHTML= paginacargando;
    }

    var datos= crearDatosSolicitudListadoInterConsulta(
        filtrosSolicitud,
        esCargaListado ? "buscarInterConsultas" : "buscarInterConsultasEnriquecidos"
    );
    var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmInterConsulta.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		 xhr: function () {
        var xhr = new window.XMLHttpRequest();
        //Uload progress
        xhr.upload.addEventListener("progress" ,function (evt) {
         var kb=((evt.loaded*1)/1000).toFixed(1)
		
		 if(kb=="0.0"){
			kb=0.1;
		}
                     
        }, false);
 //Download progress
		xhr.addEventListener("progress", function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
			kb=0.1;
		}
                    
        }, false);
        return xhr;
    },
		error: function (jqXHR, textstatus, errorThrowm) {
            if (textstatus == "abort") {
                return;
            }
            manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
            ver_vetana_informativa("SE HA PRODUCTIDO UN ERROR");
            controldebusquedadInformeInterConsulta= false;
            actualizarBotonCargarMasHilosInterConsulta();
		},
		success: function (responseText) {
			if (esCargaListado && (token != secuenciaListadoInterConsulta || busquedaInterConsultaCancelada)) {
                return;
            }
			try {
                var datos = $.parseJSON(responseText);
				var respuestaValida=respuestaJqueryAjax(datos["1"])
				if (respuestaValida) {
                    if (esCargaListado && (token != secuenciaListadoInterConsulta || busquedaInterConsultaCancelada)) {
                        actualizarBotonCargarMasHilosInterConsulta();
                        return;
                    }

                    verificarMensajesPendientesListadoInterConsulta(datos["3"]);

                    // Una busqueda unica
                    if (limite == 0) {
                        if (!cod_usuarioFK) {
                            document.getElementById('listAsuntoAbmInterConsulta').innerHTML= datos[8];
                        } else {
                            if (datos["6"] > 0) {
                                document.getElementById('avisoMensajesPendientes').style.display= "";
                            } else {
                                document.getElementById('avisoMensajesPendientes').style.display= "none";
                            }
    
                            // Evalua si existen interconsultas sin cerrar
                            if (Number(datos["7"]) > 0) {
                                document.getElementById('avisoInterconsultasAbiertos').style.display= "";
                                document.getElementById('avisoInterconsultasAbiertos').innerHTML= "Tienes "+datos[7]+" interconsultas sin cerrar.";
                            } else {
                                document.getElementById('avisoInterconsultasAbiertos').style.display= "none";
                            }
                        }
                    } else {
                        aplicarPaginaListadoInterConsulta(datos, 0, false);
                        cargarEnriquecimientoListadoInterConsulta(filtrosSolicitud, token, 0, datos["12"] || []);
                    }
				}
			} catch (error) {
                actualizarBotonCargarMasHilosInterConsulta();
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
                var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		},
        complete: function() {
            if (solicitudListadoInterConsultaActiva === OpAjax) {
                solicitudListadoInterConsultaActiva= null;
            }
        }
	});
    if (esCargaListado) {
        solicitudListadoInterConsultaActiva= OpAjax;
    }
}

function crearHilosSeguimientoPacienteHistorico() {
    if (!confirm("Se van a crear o actualizar hilos maestros por numero de cedula para todas las ventas reales historicas. No se duplican hilos existentes. Desea continuar?")) {
        return;
    }

    obtener_datos_user();
    let datos= new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "crearHilosSeguimientoPacienteHistorico");
    datos.append("limite", "0");

    verCerrarEfectoCargando("1");
    $.ajax({
        data: datos,
        url: "../php_system/abmInterConsulta.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        error: function(jqXHR, textstatus) {
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
            ver_vetana_informativa("No se pudo sincronizar el historico de pacientes.", "", "error");
        },
        success: function(responseText) {
            try {
                var respuesta= $.parseJSON(responseText);
                if (respuestaJqueryAjax(respuesta["1"]) == true) {
                    var resumen= respuesta["2"] || {};
                    var mensaje= "Procesadas: " + (resumen.procesadas || 0)
                        + ". Creados: " + (resumen.creados || 0)
                        + ". Existentes actualizados: " + (resumen.existentes || 0)
                        + ". Asuntos renombrados: " + (resumen.asuntos_actualizados || 0)
                        + ". Ventas vinculadas: " + (resumen.ventas_vinculadas || 0)
                        + ". Hilos unificados: " + (resumen.hilos_unificados || 0)
                        + ". Conflictos: " + (resumen.conflictos || 0)
                        + ". Sin plan madre: " + (resumen.con_ventas_sin_plan_madre || 0)
                        + ".";
                    ver_vetana_informativa("Sincronizacion finalizada", mensaje, "info");
                    seleccionarCategoriaHilosInterConsulta("administrativo_clinico");
                } else {
                    ver_vetana_informativa("No se pudo sincronizar el historico de pacientes.", "", "error");
                }
            } catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ");
                GuardarArchivosLog("Error: " + error + " \r\n Consola: " + responseText);
            }
        },
        complete: function() {
            verCerrarEfectoCargando("");
        }
    });
}

function textoResumenUnificacionSeguimientoPaciente(resumen) {
    const principal= resumen && resumen.principal ? resumen.principal : {};
    const totales= resumen && resumen.totales ? resumen.totales : {};
    const hilos= resumen && resumen.hilos ? resumen.hilos : [];
    const destinoTexto= Number(principal.cod_interConsulta || 0) > 0
        ? ("#" + principal.cod_interConsulta + " - " + textoDetalleHilo(principal.asunto_actual || principal.asunto_maestro, "Hilo maestro"))
        : ("Se creara: " + textoDetalleHilo(principal.asunto_maestro, "Hilo maestro"));

    let lineas= [
        "Se va a unificar el seguimiento por cedula.",
        "",
        "Paciente: " + textoDetalleHilo(principal.nombre_paciente, "Sin nombre"),
        "CI: " + textoDetalleHilo(principal.cedula, "Sin cedula"),
        "Hilo maestro: " + destinoTexto,
        "",
        "Hilos que quedaran inactivos: " + (totales.hilos || hilos.length || 0),
        "Mensajes a mover: " + (totales.mensajes || 0),
        "Gastos a mover: " + (totales.gastos || 0),
        "Dictamenes a mover: " + (totales.dictamenes || 0),
        "",
        "No se eliminan datos. El historial se mueve al hilo maestro."
    ];

    if (Number(principal.conflicto || 0) > 0) {
        lineas.unshift(
            "AVISO: esta cedula esta asociada a mas de un paciente.",
            textoDetalleHilo(principal.detalle_conflicto, "Revise los datos antes de continuar."),
            ""
        );
    }

    if ((totales.hilos || hilos.length || 0) == 0) {
        lineas.push("", "No se encontraron otros hilos para unificar.");
    }

    return lineas.join("\n");
}

function previsualizarUnificacionSeguimientoPaciente() {
    if (controlacceso("FUSIONARINTERCONSULTA", "accion") == false) {
        return;
    }
    if (!cod_interConsulta) {
        ver_vetana_informativa("Faltan datos", "Abra primero un hilo para unificar su seguimiento.", "advertencia");
        return;
    }

    obtener_datos_user();
    let datos= new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "previsualizarUnificacionSeguimientoPaciente");
    datos.append("cod_interConsulta", cod_interConsulta);

    verCerrarEfectoCargando("1");
    var ejecucionIniciada= false;
    $.ajax({
        data: datos,
        url: "../php_system/abmInterConsulta.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        error: function(jqXHR, textstatus) {
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
            ver_vetana_informativa("No se pudo preparar la unificacion.", "", "error");
        },
        success: function(responseText) {
            try {
                var respuesta= $.parseJSON(responseText);
                if (respuesta["1"] != "exito") {
                    var errorResumen= respuesta["2"] || {};
                    ver_vetana_informativa(
                        "No se puede unificar",
                        textoDetalleHilo(errorResumen.mensaje, "El hilo no tiene una venta o cedula valida para seguimiento."),
                        "advertencia"
                    );
                    return;
                }

                var resumen= respuesta["2"] || {};
                var confirmarConflicto= Number(resumen.requiere_confirmacion_conflicto || 0) > 0;
                if (!confirm(textoResumenUnificacionSeguimientoPaciente(resumen))) {
                    return;
                }
                ejecucionIniciada= true;
                ejecutarUnificacionSeguimientoPaciente(confirmarConflicto ? "1" : "0");
            } catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ");
                GuardarArchivosLog("Error: " + error + " \r\n Consola: " + responseText);
            }
        },
        complete: function() {
            if (!ejecucionIniciada) {
                verCerrarEfectoCargando("");
            }
        }
    });
}

function ejecutarUnificacionSeguimientoPaciente(confirmarConflicto) {
    obtener_datos_user();
    let datos= new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "unificarSeguimientoPaciente");
    datos.append("cod_interConsulta", cod_interConsulta);
    datos.append("confirmar_conflicto", confirmarConflicto || "0");

    verCerrarEfectoCargando("1");
    $.ajax({
        data: datos,
        url: "../php_system/abmInterConsulta.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        error: function(jqXHR, textstatus) {
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
            ver_vetana_informativa("No se pudo ejecutar la unificacion.", "", "error");
        },
        success: function(responseText) {
            try {
                var respuesta= $.parseJSON(responseText);
                var resultado= respuesta["2"] || {};
                if (respuesta["1"] == "exito") {
                    var mensaje= "Hilos unificados: " + (resultado.hilos_unificados || 0)
                        + ". Ventas vinculadas: " + (resultado.ventas_vinculadas || 0) + ".";
                    ver_vetana_informativa("Unificacion finalizada", mensaje, "info");
                    if (resultado.cod_interConsulta_destino) {
                        buscarInterConsultasYContenido(resultado.cod_interConsulta_destino);
                    }
                    buscarPacientesConInterConsultas();
                    return;
                }

                if (resultado.motivo == "requiere_confirmacion_conflicto") {
                    ver_vetana_informativa(
                        "Conflicto de cedula",
                        textoDetalleHilo(resultado.detalle_conflicto || resultado.mensaje, "Revise la cedula duplicada antes de continuar."),
                        "advertencia"
                    );
                    return;
                }

                ver_vetana_informativa(
                    "No se pudo unificar",
                    textoDetalleHilo(resultado.mensaje, "Ocurrio un error al mover los datos del seguimiento."),
                    "error"
                );
            } catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ");
                GuardarArchivosLog("Error: " + error + " \r\n Consola: " + responseText);
            }
        },
        complete: function() {
            verCerrarEfectoCargando("");
        }
    });
}

function desplazarListadoHilosAlInicio() {
    var contenedor= document.querySelector("#divListadoInterConsulta .hilos-thread-table-wrap");
    if (contenedor) {
        contenedor.scrollTop= 0;
    }
    var listado= document.getElementById("table_frm_VistaInterConsulta");
    if (listado && listado.parentElement) {
        listado.parentElement.scrollTop= 0;
    }
}

function cargarPaginaHilosInterConsulta(offsetPagina) {
    if (!filtrosUltimaBusquedaInterConsulta) {
        buscarPacientesConInterConsultas();
        return;
    }

    var total= Number(totalregistroinformeInterConsulta) || 0;
    offsetPagina= Math.max(0, Number(offsetPagina) || 0);
    if (total > 0) {
        offsetPagina= Math.min(offsetPagina, Math.max(total - 1, 0));
        offsetPagina= Math.floor(offsetPagina / limiteMaximoListadoInterConsulta) * limiteMaximoListadoInterConsulta;
    }

    if (offsetPagina == paginaOffsetInterConsulta) {
        actualizarBotonCargarMasHilosInterConsulta();
        return;
    }

    var botonSiguiente= document.getElementById("btnCargarMasHilosInterConsulta");
    var botonAnterior= document.getElementById("btnPaginaAnteriorHilosInterConsulta");
    var progreso= document.getElementById("divProgressInformeInterConsulta");
    var proceso= document.getElementById("tbProcessInformeInterConsulta");
    var etiqueta= document.getElementById("lblProcessInformeInterConsulta");
    var porce= total > 0 ? ((offsetPagina * 100) / total).toFixed(0) : 0;
    var paginaDestino= Math.floor(offsetPagina / limiteMaximoListadoInterConsulta) + 1;

    if (botonSiguiente) {
        botonSiguiente.disabled= true;
        botonSiguiente.value= "Cargando...";
    }
    if (botonAnterior) {
        botonAnterior.disabled= true;
    }

    if (etiqueta) {
        etiqueta.textContent= "Cargando pagina " + paginaDestino;
    }

    if (proceso) {
        proceso.style.display= "";
    }

    if (progreso) {
        progreso.style.width= porce + "%";
        progreso.style.backgroundColor= "rgb(76, 175, 80)";
    }

    busquedaInterConsultaCancelada= false;
    controldebusquedadInformeInterConsulta= true;

    var filtros= filtrosUltimaBusquedaInterConsulta;
    buscarMasPacientesConInterConsultas2(
        filtros.cod_interC,
        filtros.cod_usuarioFK,
        filtros.asunto,
        filtros.nombre_responsable,
        filtros.nombre_cliente,
        filtros.estado,
        filtros.tipo,
        filtros.cod_localFK,
        limitePaginaHilosInterConsulta(offsetPagina),
        filtros.ocultar_inactivos,
        filtros.usuario_vinculado,
        filtros.busqueda_global,
        filtros.fecha_desde,
        filtros.fecha_hasta,
        filtros.categoria_principal,
        offsetPagina
    );
}

function cargarMasHilosInterConsulta() {
    if (!puedeCargarMasHilosInterConsulta()) {
        actualizarBotonCargarMasHilosInterConsulta();
        return;
    }
    cargarPaginaHilosInterConsulta((Number(paginaOffsetInterConsulta) || 0) + limiteMaximoListadoInterConsulta);
}

function cargarPaginaAnteriorHilosInterConsulta() {
    if (!puedeRetrocederHilosInterConsulta()) {
        actualizarBotonCargarMasHilosInterConsulta();
        return;
    }
    cargarPaginaHilosInterConsulta((Number(paginaOffsetInterConsulta) || 0) - limiteMaximoListadoInterConsulta);
}

function buscarMasPacientesConInterConsultas2(cod_interC, cod_usuarioFK, asunto, nombre_responsable, nombre_cliente, estado, tipo, cod_localFK, limite, ocultar_inactivos, usuario_vinculado, busqueda_global= "", fecha_desde= "", fecha_hasta= "", categoria_principal= "", offsetPagina= 0) {
    categoria_principal= categoria_principal || obtenerCategoriaActivaInterConsulta();

    if (!controldebusquedadInformeInterConsulta) {
        return;
    }

    var filtrosSolicitud= {
        cod_interC: cod_interC,
        cod_usuarioFK: cod_usuarioFK,
        asunto: asunto,
        nombre_responsable: nombre_responsable,
        nombre_cliente: nombre_cliente,
        estado: estado,
        tipo: tipo,
        usuario_vinculado: usuario_vinculado,
        cod_localFK: cod_localFK,
        busqueda_global: busqueda_global,
        fecha_desde: fecha_desde,
        fecha_hasta: fecha_hasta,
        categoria_principal: categoria_principal,
        ocultar_inactivos: ocultar_inactivos,
        limite: limite
    };
    var token= ++secuenciaListadoInterConsulta;
    abortarSolicitudHilosInterConsulta(solicitudListadoInterConsultaActiva);
    abortarSolicitudHilosInterConsulta(solicitudEnriquecimientoListadoInterConsultaActiva);
    var datos= crearDatosSolicitudListadoInterConsulta(filtrosSolicitud, "buscarInterConsultas");

    var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmInterConsulta.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		 xhr: function () {
        var xhr = new window.XMLHttpRequest();
        //Uload progress
        xhr.upload.addEventListener("progress" ,function (evt) {
         var kb=((evt.loaded*1)/1000).toFixed(1)
		
		 if(kb=="0.0"){
			kb=0.1;
		}
                     
        }, false);
 //Download progress
		xhr.addEventListener("progress", function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
			kb=0.1;
		}
                    
        }, false);
        return xhr;
    },
		error: function (jqXHR, textstatus, errorThrowm) {
            if (textstatus == "abort") {
                return;
            }
            manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
            ver_vetana_informativa("SE HA PRODUCTIDO UN ERROR");
            controldebusquedadInformeInterConsulta= false;
            actualizarBotonCargarMasHilosInterConsulta();
		},
		success: function (responseText) {
			if (token != secuenciaListadoInterConsulta || busquedaInterConsultaCancelada) {
                return;
            }
			try {
				var datos = $.parseJSON(responseText);
                var respuestaValida=respuestaJqueryAjax(datos["1"])
				if (respuestaValida) {
                    if (token != secuenciaListadoInterConsulta || busquedaInterConsultaCancelada) {
                        actualizarBotonCargarMasHilosInterConsulta();
                        return;
                    }

                    aplicarPaginaListadoInterConsulta(datos, offsetPagina, false);
                    cargarEnriquecimientoListadoInterConsulta(filtrosSolicitud, token, offsetPagina, datos["12"] || []);
				}
			} catch (error) {
                document.getElementById("divProgressInformeInterConsulta").style.backgroundColor='#ff5722'
                controldebusquedadInformeInterConsulta= false;
                actualizarBotonCargarMasHilosInterConsulta();
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
                var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
            }
		},
        complete: function() {
            if (solicitudListadoInterConsultaActiva === OpAjax) {
                solicitudListadoInterConsultaActiva= null;
            }
        }
	});
    solicitudListadoInterConsultaActiva= OpAjax;
}

function solicitarMencionInterConsulta(cod_interC) {
    let datos= new FormData();
    datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", 'solicitarAcceso');
    datos.append("nombre_usuario", document.getElementById("lblUser").textContent);
    datos.append("cod_interConsulta", cod_interC);
    
    verCerrarEfectoCargando("1");
    var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmInterConsulta.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		 xhr: function () {
        var xhr = new window.XMLHttpRequest();
        //Uload progress
        xhr.upload.addEventListener("progress" ,function (evt) {
         var kb=((evt.loaded*1)/1000).toFixed(1)
		
		 if(kb=="0.0"){
			kb=0.1;
		}
                     
        }, false);
 //Download progress
		xhr.addEventListener("progress", function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
			kb=0.1;
		}
                    
        }, false);
        return xhr;
    },
		error: function (jqXHR, textstatus, errorThrowm) {
	        verCerrarEfectoCargando("");
            manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
            ver_vetana_informativa("SE HA PRODUCTIDO UN ERROR");
		},
		success: function (responseText) {
			Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta == "exito") {
					ver_vetana_informativa("Solicitud enviada.", "", "info");
				}
			} catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
                var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			} finally {
                verCerrarEfectoCargando("");
            }
		}
	});
}

function fusionarInterConsultas(id_interconsulta_destino) {
    if(controlacceso("FUSIONARINTERCONSULTA","accion")==false){ return;}
    if(!confirm("¿Esta seguro que desea fusionar el hilo "+cod_interConsulta+" con "+id_interconsulta_destino+"?")) { return;}
    
    let datos= new FormData();
    datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", 'fusionarInterConsultas');
    datos.append("cod_interConsulta_destino", id_interconsulta_destino);
    datos.append("cod_interConsulta", cod_interConsulta);
    
    verCerrarEfectoCargando("1");
    var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmInterConsulta.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		 xhr: function () {
        var xhr = new window.XMLHttpRequest();
        //Uload progress
        xhr.upload.addEventListener("progress" ,function (evt) {
         var kb=((evt.loaded*1)/1000).toFixed(1)
		
		 if(kb=="0.0"){
			kb=0.1;
		}
                     
        }, false);
 //Download progress
		xhr.addEventListener("progress", function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
			kb=0.1;
		}
                    
        }, false);
        return xhr;
    },
		error: function (jqXHR, textstatus, errorThrowm) {
	        verCerrarEfectoCargando("");
            manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
            ver_vetana_informativa("SE HA PRODUCTIDO UN ERROR");
		},
		success: function (responseText) {
			Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta == "exito") {
					ver_vetana_informativa("Datos guardados.", "", "info");
                    verCerrarVentanaInterConsulta(false, 'divListadoInterConsulta');
                    verCerrarVentanaDetalleInterConsulta(false);
                    //ventanaAnterior= ventanaAnterior.pop();
				}
			} catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
                var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			} finally {
                verCerrarEfectoCargando("");
            }
		}
	});
}

function verificarCamposInterConsulta() {
    const asunto= document.getElementById('inptAsuntoAbmInterConsulta').value;
    const estado= document.getElementById('inptEstadoAbmInterConsulta').value;
    const tipo= document.getElementById('inptTipoAbmInterConsulta').value;
    const categoriaSeleccionada= valorCampoInterConsulta("inptCategoriaAbmInterConsulta") || obtenerCategoriaPrincipalHilo(tipo) || obtenerCategoriaActivaInterConsulta();
    const local= document.getElementById('inptLocalAbmInterConsulta').value;
    const monto_limite= document.getElementById('inptMontoLimiteAbmInterConsulta').value.replace('.', '');
    const observacion= document.getElementById('inptObservacionAbmInterConsulta').value;

    if (String(cod_ventaFKConsulta || "").trim() != "" && String(cod_ventaFKConsulta || "").trim() != "0") {
        mostrarAvisoSeguimientoPacienteAutomatico();
        return false;
    }

    // Verifica si el campo para fusionar esta vacio
    const id_interconsulta_destino = document.getElementById('inptCodInterc1AbmInterConsulta').value;
    if (id_interconsulta_destino) {
        fusionarInterConsultas(id_interconsulta_destino);
        return;
    }
    
    if (!asunto) {
        ver_vetana_informativa("Faltan datos", "El campo asunto es obligatorio para crear una nueva Interconsulta.", "advertencia");
        return false;
    }
    if (!local) {
        ver_vetana_informativa("Faltan datos", "Falto seleccionar el local", "advertencia");
        return false;
    }

    if (cod_interConsulta && categoriaOriginalAbmInterConsulta && categoriaOriginalAbmInterConsulta != categoriaSeleccionada) {
        var categoriaAnterior= categoriasHilosInterConsulta[categoriaOriginalAbmInterConsulta] ? categoriasHilosInterConsulta[categoriaOriginalAbmInterConsulta].nombre : categoriaOriginalAbmInterConsulta;
        var categoriaNueva= categoriasHilosInterConsulta[categoriaSeleccionada] ? categoriasHilosInterConsulta[categoriaSeleccionada].nombre : categoriaSeleccionada;
        if (!confirm("Esta cambiando la categoria principal del hilo de " + categoriaAnterior + " a " + categoriaNueva + ". El historial y los mensajes se conservan. Desea continuar?")) {
            return false;
        }
    }

    // Verificar si el asunto es uno seleccionado del datalist o no
    const datalist = document.getElementById('listAsuntoAbmInterConsulta');
    if (datalist && !cod_interConsulta) {
        const optionCoincidente = Array.from(datalist.options).find(opt => opt.value === asunto);

        if (optionCoincidente && !confirm("Ya existe una interconsulta con ese nombre, desea crearlo de todos modos?")) {
            if (confirm(`¿Desea solicitar ingresar a la conversación de ${asunto}?`)) {
                solicitarMencionInterConsulta(optionCoincidente.dataset.id);
                verCerrarVentanaInterConsulta(false);
            }
            return false;
        }
    }
    
    abmInterConsulta(asunto, estado, tipo, local, monto_limite, observacion);
}

function mostrarAvisoSeguimientoPacienteAutomatico() {
    const titulo= "Seguimiento automatico";
    const mensaje= "El seguimiento por paciente se genera automaticamente desde la venta. Use el hilo maestro existente.";
    if (typeof ver_vetana_informativa == "function") {
        ver_vetana_informativa(titulo, mensaje, "advertencia");
    } else {
        alert(titulo + "\n\n" + mensaje);
    }
}

function abmInterConsulta(asunto, estado, tipo, local, monto_limite, observacion) {
    // Verificar si se crea o se edita la interconsulta
    if (cod_interConsulta) {
        if(controlacceso("EDITARINTERCONSULTA","accion")==false){ return;}
    } else {
        if(controlacceso("CREARINTERCONSULTA","accion")==false){ return;}
    }

    // Limpia el formato del monto_limite
    monto_limite= monto_limite.replace(".", "").replace(" ", "");
    if (monto_limite == "") {
        monto_limite= "0";
    }
    var categoriaGuardada= valorCampoInterConsulta("inptCategoriaAbmInterConsulta") || obtenerCategoriaPrincipalHilo(tipo) || obtenerCategoriaActivaInterConsulta();

    let datos= new FormData();
    datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", 'nuevo/editar interconsulta');
    datos.append("estado", estado);
    datos.append("asunto", asunto);
    datos.append("observacion", observacion);
    datos.append("tipo", tipo);
    datos.append("cod_interConsulta", cod_interConsulta);
    datos.append("cod_ventaFK", cod_ventaFKConsulta);
    datos.append("cod_localFK", local);
    datos.append("monto_limite", monto_limite);

    verCerrarEfectoCargando("1");
    var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmInterConsulta.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		 xhr: function () {
        var xhr = new window.XMLHttpRequest();
        //Uload progress
        xhr.upload.addEventListener("progress" ,function (evt) {
         var kb=((evt.loaded*1)/1000).toFixed(1)
		
		 if(kb=="0.0"){
			kb=0.1;
		}
                     
        }, false);
 //Download progress
		xhr.addEventListener("progress", function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
			kb=0.1;
		}
                    
        }, false);
        return xhr;
    },
		error: function (jqXHR, textstatus, errorThrowm) {
	        verCerrarEfectoCargando("");
            manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
            ver_vetana_informativa("SE HA PRODUCTIDO UN ERROR");
		},
		success: function (responseText) {
			Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta == "exito") {
					ver_vetana_informativa("Datos guardados.", "", "info");

                    // Busca el cod_interConsulta recien creado
                    if (categoriaGuardada == obtenerCategoriaActivaInterConsulta()) {
                        document.getElementById('inptBuscarInterConsulta1').value= datos["2"];
                        buscarInterConsultasYContenido(datos["2"]);
                        document.getElementById('inptBuscarInterConsulta1').value= "";
                    } else {
                        verCerrarVentanaDetalleInterConsulta(false);
                        limpiarCamposDetallesInterConsulta();
                    }
                    verCerrarVentanaInterConsulta(false);
                    buscarPacientesConInterConsultas();
				} else {
					// Si el servidor responde pero con un error de aplicación (ej: error en la BD)
                    const errorServidor= datos["2"] || {};
					const mensajeError = errorServidor.mensaje || datos["mensaje"] || "El servidor no pudo procesar la solicitud.";
					ver_vetana_informativa("No se pudo guardar", mensajeError, "advertencia");
				}
			} catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
                var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			} finally {
                verCerrarEfectoCargando("");
            }
		}
	});
}

function buscarMensajes() {
    let datos= new FormData();
    datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", 'buscarVistaMensajes');
    datos.append("cod_interConsulta", cod_interConsulta);
    datos.append("limite", 10);

    verCerrarEfectoCargando("1");
    var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmInterConsulta.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		 xhr: function () {
        var xhr = new window.XMLHttpRequest();
        //Uload progress
        xhr.upload.addEventListener("progress" ,function (evt) {
         var kb=((evt.loaded*1)/1000).toFixed(1)
		
		 if(kb=="0.0"){
			kb=0.1;
		}
                     
        }, false);
 //Download progress
		xhr.addEventListener("progress", function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
			kb=0.1;
		}
                    
        }, false);
        return xhr;
    },
		error: function (jqXHR, textstatus, errorThrowm) {
	        verCerrarEfectoCargando("");
            manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
            ver_vetana_informativa("SE HA PRODUCTIDO UN ERROR");
		},
		success: function (responseText) {
			Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta == "exito") {
					ver_vetana_informativa("Datos guardados.", "", "info");
				} else {
					// Si el servidor responde pero con un error de aplicación (ej: error en la BD)
					const mensajeError = datos["mensaje"] || "El servidor no pudo procesar la solicitud.";
					ver_vetana_informativa("Error al localizar los mensajes: " + mensajeError);
				}
			} catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
                var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			} finally {
                verCerrarEfectoCargando("");
            }
		}
	});
}

var fotoMensajeInterconsulta= "";
var extMensajeInterconsulta= "";
var tipoAdjuntoMensajeInterconsulta= "otro";
function verificarCamposMensaje() {
    const contenido= document.getElementById('inptContenidoAbmMensaje').innerHTML;
    const cod_dictamenFK= document.getElementById('dictamenAbmMensaje').value;
    if (!contenido) {
        ver_vetana_informativa("Falto ingresar un contenido");
        return false;
    }

    // Deshabilita temporalmente el boton de enviar
    document.getElementById('btnEnviarContenidoAbmMensaje').disabled= true;

    abmMensaje("", contenido, cod_dictamenFK);
}

function abmMensaje(fecha, contenido, cod_dictamenFK) {
    var hiloSolicitado= String(cod_interConsulta || "");
    var fotoSolicitada= fotoMensajeInterconsulta;
    var extensionSolicitada= extMensajeInterconsulta;
    var selectorTipoAdjunto= document.getElementById("tipoAdjuntoInterConsulta");
    var tipoAdjuntoSolicitado= selectorTipoAdjunto ? selectorTipoAdjunto.value : "otro";
    let datos= new FormData();
    datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", 'nuevo/editar mensaje');
    datos.append("fecha_creacion", fecha);
    datos.append("contenido", contenido);
    datos.append("cod_interConsulta", hiloSolicitado);
    datos.append("cod_dictamenFK", cod_dictamenFK);
    var codMensajeRespuesta= document.getElementById("codMensajeRespuestaInterConsulta");
    if (codMensajeRespuesta && codMensajeRespuesta.value) {
        datos.append("cod_mensaje_respuestaFK", codMensajeRespuesta.value);
    }

    verCerrarEfectoCargando("1");
    var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmInterConsulta.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		 xhr: function () {
        var xhr = new window.XMLHttpRequest();
        //Uload progress
        xhr.upload.addEventListener("progress" ,function (evt) {
         var kb=((evt.loaded*1)/1000).toFixed(1)
		
		 if(kb=="0.0"){
			kb=0.1;
		}
                     
        }, false);
 //Download progress
		xhr.addEventListener("progress", function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
			kb=0.1;
		}
                    
        }, false);
        return xhr;
    },
		error: function (jqXHR, textstatus, errorThrowm) {
	        verCerrarEfectoCargando("");
            manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
            ver_vetana_informativa("SE HA PRODUCTIDO UN ERROR");
            document.getElementById('btnEnviarContenidoAbmMensaje').disabled= false;
		},
		success: function (responseText) {
			Respuesta = responseText;
			console.log(Respuesta)
            document.getElementById('btnEnviarContenidoAbmMensaje').disabled= false;
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta == "exito") {
					ver_vetana_informativa("Datos guardados.", "", "info");
                    marcarMensajeLeido(false, function() {
                        subirImagenMensajeInterconsulta(datos["2"], hiloSolicitado, fotoSolicitada, extensionSolicitada, tipoAdjuntoSolicitado);
                    }, hiloSolicitado);
				} else {
                    ver_vetana_informativa("No se pudo enviar", mensajeRespuestaSeguimientoInterConsulta(datos, "Revise el mensaje e intente nuevamente."), "advertencia");
				}
			} catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
                var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			} finally {
                verCerrarEfectoCargando("");
            }
		}
	});
}

function marcarMensajeLeido(actualizarEncabezado= true, callback= null, codHilo= null) {
    var hiloSolicitado= String(codHilo || cod_interConsulta || "");
    let datos= new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", 'marcarMensajesLeido');
    datos.append("cod_interConsulta", cod_interConsulta);
    
    var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmInterConsulta.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		 xhr: function () {
        var xhr = new window.XMLHttpRequest();
        //Uload progress
        xhr.upload.addEventListener("progress" ,function (evt) {
         var kb=((evt.loaded*1)/1000).toFixed(1)
		
		 if(kb=="0.0"){
			kb=0.1;
		}
                     
        }, false);
 //Download progress
		xhr.addEventListener("progress", function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
			kb=0.1;
		}
                    
        }, false);
        return xhr;
    },
		error: function (jqXHR, textstatus, errorThrowm) {
	        verCerrarEfectoCargando("");
            manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
            ver_vetana_informativa("SE HA PRODUCTIDO UN ERROR");
            if (typeof callback == "function") {
                callback();
            }
		},
		success: function (responseText) {
			Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta != "exito") {
					ver_vetana_informativa("Error al marcar mensaje como leído.");
				} else if (actualizarEncabezado) {
                    actualizarVistaInterConsultaLeida();
                    // Actualiz la vista
                    let color= document.getElementById("contenedorEncabezadoInterConsulta").style.border;
                    color= color.substring(11);
                    document.getElementById("contenedorEncabezadoInterConsulta").style.border= "none";
                    document.getElementById("contenedorEncabezadoInterConsulta").style.borderLeft= "10px solid "+color;
                }
			} catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
                var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			} finally {
                verCerrarEfectoCargando("");
                if (typeof callback == "function") {
                    callback();
                }
            }
		}
	});
}

function limpiarcamposMensaje() {
    const fechaActual = new Date();
    const offsetMinutos = fechaActual.getTimezoneOffset();    
    const fechaLocal = new Date(fechaActual.getTime() - (offsetMinutos * 60000));

    if (document.getElementById('inptFechaAbmMensaje')) {
        document.getElementById('inptFechaAbmMensaje').value = fechaLocal.toISOString().slice(0, 16);
    }

    document.getElementById('inptContenidoAbmMensaje').innerHTML = "";
    document.getElementById('imgfotoAnexoInterchat').style.backgroundImage = "url('/GoodVentaAsisCap/iconos/subir_imagen.png')";
    document.getElementById('imgfotoAnexoInterchat').dataset.adjuntoUrl = "";
    document.getElementById('imgfotoAnexoInterchat').dataset.adjuntoExt = "";
    document.getElementById('imgfotoAnexoInterchat').dataset.adjuntoNombre = "";
    document.getElementById('imgfotoAnexoInterchat').classList.remove("imgFotoProductoDocumento");
    fotoMensajeInterconsulta = "";
    extMensajeInterconsulta = "";
    tipoAdjuntoMensajeInterconsulta = "otro";
    var selectorTipoAdjunto= document.getElementById("tipoAdjuntoInterConsulta");
    if (selectorTipoAdjunto) {
        selectorTipoAdjunto.value= "otro";
    }

    contadorLongitudMensaje= 0;
    document.getElementById('limiteCaracteresMensajeInterconsulta').innerText= contadorLongitudMensaje;
    ajustarAlturaEditorMensajeInterConsulta();

    // Limpiar campos dictamen
    document.getElementById('dictamenAbmMensaje').value= "";
    document.getElementById('inptAsuntoDictamenInterConsulta').value= "";
    cancelarRespuestaCitadaInterConsulta();
}

function subirImagenMensajeInterconsulta(cod_mens, codHilo, fotoCapturada, extensionCapturada, tipoAdjuntoCapturado) {
    var hiloSolicitado= String(codHilo || cod_interConsulta || "");
    var fotoEnviar= fotoCapturada !== undefined ? fotoCapturada : fotoMensajeInterconsulta;
    var extensionEnviar= extensionCapturada !== undefined ? extensionCapturada : extMensajeInterconsulta;
    var tipoAdjuntoEnviar= tipoAdjuntoCapturado || tipoAdjuntoMensajeInterconsulta || "otro";
    if (!(fotoEnviar && extensionEnviar)) {
        if (String(cod_interConsulta) === hiloSolicitado) {
            limpiarCamposDetallesInterConsulta();
            buscarInterConsultasYContenido(hiloSolicitado);
            limpiarcamposMensaje();
        }
        return false;
    }
    obtener_datos_user()
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", "subirImagenMensaje");
    datos.append("cod_mensaje", cod_mens);
    datos.append("foto", fotoEnviar);
    datos.append("ext", extensionEnviar);
    datos.append("tipo_adjunto", tipoAdjuntoEnviar);

    var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmInterConsulta.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		 xhr: function () {
        var xhr = new window.XMLHttpRequest();
        //Uload progress
        xhr.upload.addEventListener("progress" ,function (evt) {
         var kb=((evt.loaded*1)/1000).toFixed(1)
		
		 if(kb=="0.0"){
			kb=0.1;
		}
                     
        }, false);
 //Download progress
		xhr.addEventListener("progress", function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
			kb=0.1;
		}
                    
        }, false);
        return xhr;
    },
		error: function (jqXHR, textstatus, errorThrowm) {
	        verCerrarEfectoCargando("");
            manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
            ver_vetana_informativa("SE HA PRODUCTIDO UN ERROR");
		},
		success: function (responseText) {
			Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
                if (String(cod_interConsulta) === hiloSolicitado) {
                    limpiarCamposDetallesInterConsulta();
                    buscarInterConsultasYContenido(hiloSolicitado);
                }
				if (Respuesta != "exito") {
                    throw new Error("Error producido en subirImagenMensajeIterconsulta de JavaScript.");
                }
                if (datos.centro_facturas && !datos.centro_facturas.ok) {
                    var nombreDocumentoPendiente = datos.tipo_adjunto === "comprobante" ? "recibo" : "factura";
                    ver_vetana_informativa(
                        "Adjunto guardado; " + nombreDocumentoPendiente + " pendiente",
                        datos.centro_facturas.mensaje || "Puede completar el registro desde el adjunto del Hilo.",
                        "advertencia"
                    );
                } else if (datos.centro_facturas && datos.centro_facturas.ok) {
                    var nombreDocumentoRegistrado = datos.tipo_adjunto === "comprobante" ? "Recibo" : "Factura";
                    ver_vetana_informativa(nombreDocumentoRegistrado + " registrado", "El adjunto ya esta disponible en el Centro de Facturas.", "info");
                    if (typeof centroFacturasActualizarBadge == "function") {
                        centroFacturasActualizarBadge();
                    }
                }
                verCerrarEfectoCargando("");
                if (String(cod_interConsulta) === hiloSolicitado) {
                    limpiarcamposMensaje();
                }
			} catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
                var titulo="Error: "+error+" \r\n Consola: "+responseText
                verCerrarEfectoCargando("");
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function obtenerCuerpoDetalleInterConsulta() {
    return document.querySelector("#divAbmDetallesInterConsulta .interconsulta-detail-modal > .divMenuf");
}

function ejecutarDespuesRenderInterConsulta(callback) {
    if (window.requestAnimationFrame) {
        window.requestAnimationFrame(callback);
        return;
    }

    setTimeout(callback, 0);
}

function obtenerScrollLecturaInterConsulta(contenedor) {
    if (!contenedor) {
        return obtenerCuerpoDetalleInterConsulta();
    }

    if (contenedor.id == "table_abm_InterConsulta") {
        const estiloTimeline= window.getComputedStyle ? window.getComputedStyle(contenedor) : null;
        const timelineTieneScroll= contenedor.scrollHeight > contenedor.clientHeight;
        const timelinePuedeScroll= !estiloTimeline || estiloTimeline.overflowY == "auto" || estiloTimeline.overflowY == "scroll";
        if (timelineTieneScroll && timelinePuedeScroll) {
            return contenedor;
        }
        return obtenerCuerpoDetalleInterConsulta() || contenedor;
    }

    const panelDirecto= contenedor.matches && contenedor.matches('[data-role="dictamen-chat-panel"]') ? contenedor : null;
    const panelMensajes= panelDirecto || contenedor.querySelector('[data-role="dictamen-chat-panel"]');
    if (panelMensajes) {
        const estiloPanel= window.getComputedStyle ? window.getComputedStyle(panelMensajes) : null;
        const panelTieneScroll= panelMensajes.scrollHeight > panelMensajes.clientHeight;
        const panelPuedeScroll= !estiloPanel || estiloPanel.overflowY == "auto" || estiloPanel.overflowY == "scroll";
        if (panelTieneScroll && panelPuedeScroll) {
            return panelMensajes;
        }
    }

    return obtenerCuerpoDetalleInterConsulta() || contenedor;
}

function obtenerUltimoMensajeInterConsulta(contenedor) {
    if (!contenedor) {
        return null;
    }

    const mensajes= contenedor.querySelectorAll(".interconsulta-message-row, .interconsulta-resolution-card");
    if (!mensajes.length) {
        return null;
    }

    return mensajes[mensajes.length - 1];
}

function desplazarAlUltimoMensajeInterConsulta(contenedor, suave= false) {
    const ultimoMensaje= obtenerUltimoMensajeInterConsulta(contenedor);
    if (!ultimoMensaje) {
        return;
    }

    const scrollLectura= obtenerScrollLecturaInterConsulta(contenedor);
    if (scrollLectura && typeof scrollLectura.contains == "function" && scrollLectura.contains(ultimoMensaje)) {
        const rectScroll= scrollLectura.getBoundingClientRect();
        const rectMensaje= ultimoMensaje.getBoundingClientRect();
        const destino= Math.max(0, scrollLectura.scrollTop + (rectMensaje.bottom - rectScroll.bottom) + 8);
        if (typeof scrollLectura.scrollTo == "function") {
            scrollLectura.scrollTo({top: destino, behavior: suave ? "smooth" : "auto"});
        } else {
            scrollLectura.scrollTop= destino;
        }
        return;
    }

    if (typeof ultimoMensaje.scrollIntoView == "function") {
        ultimoMensaje.scrollIntoView({
            block: "end",
            behavior: suave ? "smooth" : "auto"
        });
    }
}

function posicionarMensajesRecientesInterConsulta(suave= false) {
    const timeline= document.getElementById("table_abm_InterConsulta");
    if (!timeline) {
        return;
    }

    timeline.querySelectorAll(".interc-dictamen-chat-pane[data-role='dictamen-chat-panel']").forEach(function(panel) {
        desplazarAlUltimoMensajeInterConsulta(panel, suave);
    });

    desplazarAlUltimoMensajeInterConsulta(timeline, suave);
}

function irUltimoMensajeInterConsulta() {
    posicionarMensajesRecientesInterConsulta(true);
}

function preservarScrollCargaAnteriorInterConsulta(scrollLectura, altoAnterior, scrollAnterior) {
    if (!scrollLectura) {
        return;
    }

    ejecutarDespuesRenderInterConsulta(function() {
        const diferenciaAlto= scrollLectura.scrollHeight - altoAnterior;
        scrollLectura.scrollTop= scrollAnterior + diferenciaAlto;
    });
}

var totalRegistroMensaje= 0;
function buscarInterConsultasYContenido(codInterConsulta, elemento = null, despuesDeCargar = null) {
    var hiloObjetivo= String(codInterConsulta || "");
    if (String(cod_interConsulta || "") !== hiloObjetivo) {
        limpiarCamposDetallesInterConsulta();
        limpiarcamposMensaje();
    }
    cod_interConsulta= hiloObjetivo;
    obtener_datos_user();
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", "buscarInterConsultasYContenido");
    datos.append("cod_interConsulta", codInterConsulta);
    datos.append("cod_clienteFK", cod_clienteFK);
    datos.append("nombre_usuario", document.getElementById("lblUser").textContent);
    datos.append("limite", 10);
    
    verCerrarEfectoCargando("1");
    document.getElementById('avisoMensajesPendientesInterConsulta').style.display= "none";
    
    document.getElementById('tituloInterConsultas').innerHTML= 'Cargando...';
    document.getElementById('tituloInterConsultas').title= 'Cargando...';
    document.getElementById('tituloInterConsultas2').innerHTML= 'Cargando...';
    document.getElementById('listadoMencionados').innerHTML= '';
    actualizarAvatarStackDetalleHilo();
    const detallesHilo= document.querySelector("#divAbmDetallesInterConsulta .interconsulta-thread-details");
    if (detallesHilo) {
        detallesHilo.removeAttribute("open");
        delete detallesHilo.dataset.resumenCargado;
        delete detallesHilo.dataset.resumenCargando;
    }
    document.getElementById('txtUsuarioCreadorInterConsulta').innerHTML= '';
    document.getElementById('txtFechaCreadorInterConsulta').innerHTML= '';
    document.getElementById('txtEstadoInterConsulta').innerHTML= '';
    document.getElementById('txtTipoInterConsulta').innerHTML= '';
    document.getElementById('txtCodInterConsulta').innerHTML= '';
    document.getElementById('localDetalleInterConsulta').innerHTML= '';
    document.getElementById('txtCodVenta').innerHTML= '';
    document.getElementById('txtMontoLimite').innerHTML= '';
    if (solicitudDetalleInterConsultaActiva && solicitudDetalleInterConsultaActiva.readyState !== 4) {
        solicitudDetalleInterConsultaActiva.abort();
    }
    var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmInterConsulta.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		 xhr: function () {
        var xhr = new window.XMLHttpRequest();
        //Uload progress
        xhr.upload.addEventListener("progress" ,function (evt) {
         var kb=((evt.loaded*1)/1000).toFixed(1)
		
		 if(kb=="0.0"){
			kb=0.1;
		}
                     
        }, false);
 //Download progress
		xhr.addEventListener("progress", function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
			kb=0.1;
		}
                    
        }, false);
        return xhr;
    },
		error: function (jqXHR, textstatus, errorThrowm) {
	        if (textstatus == "abort") {return;}
	        verCerrarEfectoCargando("");
            manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
            ver_vetana_informativa("SE HA PRODUCTIDO UN ERROR");
		},
		success: function (responseText) {
			Respuesta = responseText;
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta) {
                    // Se valida la nueva variable
                    const datosHiloRespuesta= datos["4"] || {};
                    const codInterConsultaRespuesta= textoDetalleHilo(datosHiloRespuesta.cod_interConsulta, codInterConsulta);
                    cod_interConsulta= codInterConsultaRespuesta;
                    cod_ventaFKConsulta= textoDetalleHilo(datosHiloRespuesta.cod_ventaFK, "");
                    cod_clienteConsulta= textoDetalleHilo(datosHiloRespuesta.cod_clienteFK, cod_clienteConsulta || "");

                    // Se asignan los datos del encabezado
                    document.getElementById('listadoMencionados').innerHTML= datos['6'];
                    actualizarAvatarStackDetalleHilo();

                    // Asigna loc colores
                    let colorTarjeta= "#8bc34a;";
                    let claseEstado= 'badge-success';
                    if (datos["4"]['estado'] == 'proceso') {
                        colorTarjeta=" #e53935; ";
                        claseEstado = "badge-danger";
                    } else if (datos["4"]['estado'] == 'pendiente') {
                        colorTarjeta=" #e1c247;";
                        claseEstado= "badge-warning";
                    }

                    document.getElementById('contenedorEncabezadoInterConsulta').style.setProperty("--thread-state-color", colorTarjeta.replace(";", "").trim());

                    // Se evalua si se recibio los elementos para el titulo y otros detalles
                    if (elemento) {
                        document.getElementById('inptNombreClienteAbmInterConsulta').value= $(elemento).children('#td_datos_5').html();
                    }

                    // Asigna los dictamenes al select de mensajes
                    document.getElementById('dictamenAbmMensaje').innerHTML= "<option value=''>Ninguna</option>" + datos["7"];
                    actualizarCabeceraDetalleHilo(datos["4"], datos["7"]);
                    inicializarResumenDiferidoInterConsulta();
                    const normalizacionSeguimiento= datos["8"] || {};

                    // Carga las observaciones en caso de existir
                    if (datos["4"]['observacion']) {
                        document.getElementById('divObservacionDetallesInterconsultas').style.display= "";
                        document.getElementById('divObservacionDetallesInterconsultas').innerHTML= '<span style="text-decoration: underline;"><b>Observaciones: </b></span><br>'
                        +'<span style= "font-size: 14dp;"><b>'+ datos["4"]['observacion'] + '</b></span>';
                    } else {
                        document.getElementById('divObservacionDetallesInterconsultas').style.display= "none";
                        document.getElementById('divObservacionDetallesInterconsultas').innerHTML= "";
                    }

                    document.getElementById("table_abm_InterConsulta").innerHTML= datos["2"];
                    totalRegistroMensaje = datos["5"];
                    actualizarBadgeSeguimientoDetalleDesdeTimeline();
                    cargarContextoSeguimientoProgramadoInterConsulta();

                    ejecutarDespuesRenderInterConsulta(function() {
                        if (typeof despuesDeCargar == "function" && String(cod_interConsulta) === String(codInterConsultaRespuesta)) {
                            despuesDeCargar();
                        } else {
                            posicionarMensajesRecientesInterConsulta(false);
                            enfocarSeguimientoAlertaPendienteInterConsulta();
                        }
                    });

                    // Carga diferida de secciones pesadas para acelerar el primer render.
                    cargarFlujoGastosInterConsulta(cod_interConsulta);

                    if (
                        Number(normalizacionSeguimiento.hilos_unificados || 0) > 0
                        || Number(normalizacionSeguimiento.asunto_actualizado || 0) > 0
                        || String(codInterConsultaRespuesta) != String(codInterConsulta)
                    ) {
                        buscarPacientesConInterConsultas();
                    }

                    // Actualiza los campos en gastos
                    bsExtracto = new bootstrap.Collapse(document.getElementById("collapseExtracto"), { toggle: false });
                    
                    // Busca las interconsulta de un cliente si esta asociado
                    if (cod_clienteConsulta) {
                        buscarInterConsultasAsociadasPaciente(cod_clienteConsulta);
                    }

                    // Evalua cual seria la ventana anterior
                    switch (ventanaAnterior[ventanaAnterior.length - 1]) {
                        case 'divAbmDetallesInterConsulta':
                            verCerrarVentanaInterConsulta(false);
                            //verCerrarVentanaDetalleInterConsulta(false);
                            break;
                        case 'divAbmGasto1':
                            document.getElementById('divAbmGastos').style.display= "none";
                            verCerrarVentanaDetalleInterConsulta(true, 'divAbmGastos');
                            break;
                        default:
                            break;
                    }
				} else if(datos["1"] == "NI"){
                    setTimeout(function () {
                        if (confirm(`¿Desea solicitar ingresar a la conversación?`)) {
                            solicitarMencionInterConsulta(codInterConsulta);
                        }
                    }, 500);
                }

                // Limpia campos
                fotoMensajeInterconsulta= "";
                extMensajeInterconsulta= "";

                verCerrarEfectoCargando("");
			} catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
                var tipoErrorDetalle= error && error.name ? String(error.name).replace(/[^A-Za-z]/g, "").slice(0, 40) : "Error";
                var titulo="Error JavaScript al cargar el detalle de Hilos ("+tipoErrorDetalle+").";
                console.error(titulo);
                verCerrarEfectoCargando("");
				GuardarArchivosLog(titulo)
			}
		}
	});
    solicitudDetalleInterConsultaActiva= OpAjax;
}

function cargarFlujoGastosInterConsulta(codInterConsulta) {
    const contenedor= document.getElementById("contenedorFlujoGastosInterConsulta");
    if (!contenedor) {
        return;
    }
    contenedor.innerHTML = '<div class="interconsulta-flow-state">Cargando gastos...</div>';

    obtener_datos_user();
    var datos = new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "buscarFlujoGastosInterConsulta");
    datos.append("cod_interConsulta", codInterConsulta);

    $.ajax({
        data: datos,
        url: "../php_system/abmInterConsulta.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        error: function () {
            if (String(cod_interConsulta) !== String(codInterConsulta)) {return;}
            contenedor.innerHTML = '<div class="interconsulta-flow-state interconsulta-flow-state--error">No se pudo cargar el flujo de gastos.</div>';
        },
        success: function (responseText) {console.log(responseText);
            try {
                const respuesta = $.parseJSON(responseText);
                if (String(cod_interConsulta) !== String(codInterConsulta)) {return;}
                if (respuesta["1"] === "exito") {
                    contenedor.innerHTML = respuesta["2"] || '<div class="interconsulta-flow-state">Sin gastos asociados.</div>';
                } else {
                    contenedor.innerHTML = '<div class="interconsulta-flow-state interconsulta-flow-state--error">No se pudo cargar el flujo de gastos.</div>';
                }
            } catch (error) {
                if (String(cod_interConsulta) !== String(codInterConsulta)) {return;}
                contenedor.innerHTML = '<div class="interconsulta-flow-state interconsulta-flow-state--error">No se pudo cargar el flujo de gastos.</div>';
            }
        }
    });
}

function alternarPanelDictamenInterConsulta(idPanel, boton) {
    const panel = document.getElementById(idPanel);
    if (!panel || typeof bootstrap === "undefined" || !bootstrap.Collapse) {
        return;
    }

    const seAbrira = !panel.classList.contains("show");
    const instancia = bootstrap.Collapse.getOrCreateInstance(panel, { toggle: false });
    const textoAbrir = boton ? (boton.getAttribute("data-text-open") || "Ver detalle") : "Ver detalle";
    const textoCerrar = boton ? (boton.getAttribute("data-text-close") || "Ocultar detalle") : "Ocultar detalle";

    if (seAbrira) {
        instancia.show();
    } else {
        instancia.hide();
    }

    if (boton) {
        const etiqueta = boton.querySelector("[data-label]");
        if (etiqueta) {
            etiqueta.textContent = seAbrira ? textoCerrar : textoAbrir;
        } else {
            boton.textContent = seAbrira ? textoCerrar : textoAbrir;
        }
        boton.setAttribute("aria-expanded", seAbrira ? "true" : "false");
    }
}

function cargarPanelDictamenInterConsulta(codDictamen, idPanelResolucion, idPanelMensajes, boton) {
    const panelObjetivoId= boton ? boton.getAttribute("aria-controls") : idPanelResolucion;
    const panelObjetivo= document.getElementById(panelObjetivoId);
    const panelResolucion= document.getElementById(idPanelResolucion);
    const panelMensajes= document.getElementById(idPanelMensajes);
    const tarjeta= panelObjetivo ? panelObjetivo.closest(".interc-dictamen-card") : null;
    if (!panelObjetivo || !panelResolucion || !panelMensajes || !tarjeta) {return;}

    if (tarjeta.dataset.detalleCargado == "1") {
        alternarPanelDictamenInterConsulta(panelObjetivoId, boton);
        return;
    }
    if (tarjeta.dataset.detalleCargando == "1") {return;}

    const codigoHiloSolicitud= String(cod_interConsulta || "");
    tarjeta.dataset.detalleCargando= "1";
    Array.from(tarjeta.querySelectorAll(".interc-dictamen-action-btn")).forEach(function(botonTarjeta) {
        botonTarjeta.disabled= true;
    });
    const estadoObjetivo= panelObjetivo.querySelector(".interconsulta-flow-state");
    if (estadoObjetivo) {
        estadoObjetivo.textContent= "Cargando información del dictamen...";
    }

    obtener_datos_user();
    const datos= new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "buscarDetalleDictamenInterConsulta");
    datos.append("cod_interConsulta", codigoHiloSolicitud);
    datos.append("cod_dictamen", codDictamen);

    $.ajax({
        data: datos,
        url: "../php_system/abmInterConsulta.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        error: function () {
            if (String(cod_interConsulta) == codigoHiloSolicitud) {
                const estado= panelObjetivo.querySelector(".interconsulta-flow-state");
                if (estado) {
                    estado.classList.add("interconsulta-flow-state--error");
                    estado.textContent= "No se pudo cargar el dictamen.";
                }
            }
        },
        success: function (responseText) {
            try {
                const respuesta= $.parseJSON(responseText);
                if (String(cod_interConsulta) != codigoHiloSolicitud) {return;}
                if (respuesta["1"] != "exito") {
                    throw new Error("No se pudo cargar el dictamen.");
                }
                const resolucion= panelResolucion.querySelector('[data-role="dictamen-resolucion"]');
                const chat= panelMensajes.querySelector('[data-role="dictamen-chat-panel"]');
                if (resolucion) {
                    resolucion.innerHTML= respuesta["2"] || '<div class="interconsulta-flow-state">Sin resolución registrada.</div>';
                }
                if (chat) {
                    chat.innerHTML= respuesta["3"] || '<div class="interconsulta-flow-state">Sin mensajes relacionados.</div>';
                }
                panelMensajes.dataset.totalMensajes= parseInt(respuesta["4"] || 0, 10);
                tarjeta.dataset.detalleCargado= "1";
                alternarPanelDictamenInterConsulta(panelObjetivoId, boton);
            } catch (error) {
                const estado= panelObjetivo.querySelector(".interconsulta-flow-state");
                if (estado) {
                    estado.classList.add("interconsulta-flow-state--error");
                    estado.textContent= "No se pudo cargar el dictamen.";
                }
            }
        },
        complete: function () {
            delete tarjeta.dataset.detalleCargando;
            Array.from(tarjeta.querySelectorAll(".interc-dictamen-action-btn")).forEach(function(botonTarjeta) {
                botonTarjeta.disabled= false;
            });
        }
    });
}

function verMasMensajesInterconsulta(offset, cod_dictamen) {
    obtener_datos_user()
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", "buscarMasInterConsultasYContenido");
    datos.append("cod_interConsulta", cod_interConsulta);
    datos.append("limite", "10 offset "+ offset);
    datos.append("cod_dictamenFK", cod_dictamen);

    var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmInterConsulta.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		 xhr: function () {
        var xhr = new window.XMLHttpRequest();
        //Uload progress
        xhr.upload.addEventListener("progress" ,function (evt) {
         var kb=((evt.loaded*1)/1000).toFixed(1)
		
		 if(kb=="0.0"){
			kb=0.1;
		}
                     
        }, false);
 //Download progress
		xhr.addEventListener("progress", function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
			kb=0.1;
		}
                    
        }, false);
        return xhr;
    },
		error: function (jqXHR, textstatus, errorThrowm) {
	        verCerrarEfectoCargando("");
            manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
            ver_vetana_informativa("SE HA PRODUCTIDO UN ERROR");
		},
		success: function (responseText) {
			Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta == "exito") {
                    const contenedor= "contenedorMensajesInterConsulta"+cod_dictamen;
                    const elemContenedor = document.getElementById(contenedor);

                    if (!elemContenedor) {
                        throw new Error("No se encontró el contenedor de mensajes.");
                    }

                    const scrollLectura= obtenerScrollLecturaInterConsulta(elemContenedor);
                    const altoAnterior= scrollLectura ? scrollLectura.scrollHeight : 0;
                    const scrollAnterior= scrollLectura ? scrollLectura.scrollTop : 0;
                    const panelMensajes = elemContenedor.querySelector('[data-role="dictamen-chat-panel"]') || elemContenedor;
                    const listaMensajes = elemContenedor.querySelector('[data-role="dictamen-mensajes"]');
                    const botonExistente = elemContenedor.querySelector('[data-role="dictamen-boton-mas"]');
                    console.info("botonExistente: ",botonExistente);
                    if (botonExistente) {
                        botonExistente.remove();
                    }

                    const totalMensajesContenedor = parseInt(elemContenedor.dataset.totalMensajes || totalRegistroMensaje, 10);
                    const siguienteOffset = parseInt(offset, 10) + 10;
                    let btnMasMensajes= "";
                    if (siguienteOffset < totalMensajesContenedor) {
                        btnMasMensajes= "<div data-role='dictamen-boton-mas' style='width: 100%; display: flex; justify-content: center; margin-bottom: 12px;'>"+
                                "<button class='btn btn-success' onclick='verMasMensajesInterconsulta("+siguienteOffset+", "+JSON.stringify(String(cod_dictamen ?? ""))+")'>Ver más mensajes...</button>"+
                            "</div>";
                    }
                    
                    if (listaMensajes) {
                        listaMensajes.innerHTML = datos["2"] + listaMensajes.innerHTML;
                        if (btnMasMensajes) {
                            panelMensajes.insertAdjacentHTML("afterbegin", btnMasMensajes);
                        }
                    } else {
                        elemContenedor.innerHTML = btnMasMensajes + datos["2"] + elemContenedor.innerHTML;
                    }
                    preservarScrollCargaAnteriorInterConsulta(scrollLectura, altoAnterior, scrollAnterior);
				} else {
                    throw new Error("Error producido en buscarMasInterConsultas de JavaScript.");
                }
			} catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
                var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			} finally {
                verCerrarEfectoCargando("");
            }
		}
	});
}

function buscarMasInterConsultas() {
    const asunto= document.getElementById("inptAsuntoInterConsulta").value;
    const paciente= document.getElementById("inptPacienteInterConsulta").value;
    const cod_asunto= document.getElementById("inptBuscarAbmInterConsulta1").value;
    const tipo= document.getElementById("inptBuscarAbmInterConsulta2").value;
    const estado= document.getElementById("inptBuscarAbmInterConsulta3").value;

    obtener_datos_user()
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", "buscarInterConsultas");
    datos.append("asunto", asunto);
    datos.append("paciente", paciente);
    datos.append("cod_asunto", cod_asunto);
    datos.append("tipo", tipo);
    datos.append("estado", estado);
    datos.append("limite", "10 OFFSET "+registrocargadoInterConsulta);
    datos.append("cod_dictamenFK", cod_dictamen);

    var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmInterConsulta.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		 xhr: function () {
        var xhr = new window.XMLHttpRequest();
        //Uload progress
        xhr.upload.addEventListener("progress" ,function (evt) {
         var kb=((evt.loaded*1)/1000).toFixed(1)
		
		 if(kb=="0.0"){
			kb=0.1;
		}
                     
        }, false);
 //Download progress
		xhr.addEventListener("progress", function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
			kb=0.1;
		}
                    
        }, false);
        return xhr;
    },
		error: function (jqXHR, textstatus, errorThrowm) {
	        verCerrarEfectoCargando("");
            manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
            ver_vetana_informativa("SE HA PRODUCTIDO UN ERROR");
		},
		success: function (responseText) {
			Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta == "exito") {
					document.getElementById("table_abm_InterConsulta").innerHTML= datos["2"];
                    totalregistroinformeInterConsulta= parseInt(datos["5"]);
					registrocargadoInterConsulta= parseInt(datos["4"]);
					
					// Controla el progreso de la busqueda
					if(totalregistroinformeInterConsulta>registrocargadoInterConsulta){
						controldebusquedadInformeInterConsulta=true;
						var porce=((registrocargadoInterConsulta*100)/totalregistroinformeInterConsulta).toFixed(0)
                        document.getElementById("tbProcessInformeInterConsulta").style.display= "";
						document.getElementById("divProgressInformeInterConsulta").style.width=porce+"%";
						document.getElementById("divProgressInformeInterConsulta").style.backgroundColor="rgb(76, 175, 80)";

                        buscarMasInterConsultas();
                    }else{
                        document.getElementById("tbProcessInformeInterConsulta").style.display= "none";
                        controldebusquedadInformeInterConsulta=false
                    }
				} else {
                    throw new Error("Error producido en buscarMasInterConsultas de JavaScript.");
                }
			} catch (error) {
				controldebusquedadInformeAsistencia=false;
				document.getElementById("divProgressInformeAsistencia").style.backgroundColor='#ff5722'

                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
                var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			} finally {
                verCerrarEfectoCargando("");
            }
		}
	});
}

function eliminarMencionMensaje(cod_mencion) {
    // Mostrar dialogo de advertencia antes de continuar
    if (!confirm("¿Está seguro de que desea eliminar esta mencion?")) {
        return;
    }
    
    let datos= new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", 'eliminarMencionMensaje');
    datos.append("cod_mencion", cod_mencion);
    datos.append("cod_interConsulta", cod_interConsulta);

    verCerrarEfectoCargando("1");
    var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmInterConsulta.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		 xhr: function () {
        var xhr = new window.XMLHttpRequest();
        //Uload progress
        xhr.upload.addEventListener("progress" ,function (evt) {
         var kb=((evt.loaded*1)/1000).toFixed(1)
		
		 if(kb=="0.0"){
			kb=0.1;
		}
                     
        }, false);
 //Download progress
		xhr.addEventListener("progress", function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
			kb=0.1;
		}
                    
        }, false);
        return xhr;
    },
		error: function (jqXHR, textstatus, errorThrowm) {
	        verCerrarEfectoCargando("");
            manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
            ver_vetana_informativa("SE HA PRODUCTIDO UN ERROR");
		},
		success: function (responseText) {
			Respuesta = responseText;
			console.log(Respuesta);
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta == "exito") {
					buscarInterConsultasYContenido(cod_interConsulta);
				} else {
                    throw new Error("Error producido en eliminarMencionMensaje de JavaScript.");
                }
			} catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
                var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			} finally {
                verCerrarEfectoCargando("");
            }
		}
	});
}

function mostrarOpcionesUsuariosMenciones(textarea, sugerencias) {
  // Crear un div flotante debajo del textarea
  let dropdown = document.getElementById('dropdown-menciones');
  if (!dropdown) {
    dropdown = document.createElement('div');
    dropdown.id = 'dropdown-menciones';
    document.body.appendChild(dropdown);
  }

  dropdown.style.position = 'fixed';
  dropdown.style.background = '#fff';
  dropdown.style.border = '1px solid #cdd9e4';
  dropdown.style.borderRadius = '8px';
  dropdown.style.boxShadow = '0 12px 28px rgba(15, 23, 42, 0.22)';
  dropdown.style.zIndex = 1000001;
  dropdown.style.maxHeight = '260px';
  dropdown.style.overflowY = 'auto';
  dropdown.style.padding = '4px';
  dropdown.style.display = '';
  
  // Posicionar debajo del textarea
  const rect = textarea.getBoundingClientRect();
  dropdown.style.left = rect.left + 'px';
  dropdown.style.top = rect.bottom + 'px';
  dropdown.style.width = Math.max(rect.width, 260) + 'px';
  
  // Rellenar con sugerencias
  dropdown.innerHTML = '';
  if (sugerencias.length == 0) {
    const itemVacio = document.createElement('div');
    itemVacio.textContent = 'Sin usuarios disponibles';
    itemVacio.style.padding = '10px 12px';
    itemVacio.style.color = '#64748b';
    itemVacio.style.fontSize = '13px';
    dropdown.appendChild(itemVacio);
    return;
  }

  sugerencias.forEach(([id_sug, nombre]) => {
    const item = document.createElement('div');
    item.textContent = nombre;
    item.id= id_sug;
    item.className = 'menciones-mensaje';
    item.style.padding = '8px 10px';
    item.style.cursor = 'pointer';
    item.style.borderRadius = '6px';
    item.style.color = '#1d4ed8';
    item.style.borderBottom = '0';
    item.onmouseenter = () => {
      item.style.background = '#eef6ff';
    };
    item.onmouseleave = () => {
      item.style.background = '';
    };
    item.onmousedown = (event) => {
      event.preventDefault();
      // Reemplazar el @texto por el nombre completo
      textarea.innerHTML = textarea.innerHTML.replace(
        /@\S*$/, 
        '<b class="menciones-mensaje" id="'+id_sug+'">@' + nombre + '</b>&nbsp;'
      );
      dropdown.innerHTML = '';
      dropdown.style.display = 'none';
      textarea.focus();
    };
    dropdown.appendChild(item);
  });
}

function buscarListadoInterConsultas() {
    obtener_datos_user();
    var datos = new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "buscarVistaMensajesSeleccionar");
    datos.append("cod_interConsulta", cod_interConsulta);

    verCerrarEfectoCargando("1");
    var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmInterConsulta.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		 xhr: function () {
        var xhr = new window.XMLHttpRequest();
        //Uload progress
        xhr.upload.addEventListener("progress" ,function (evt) {
         var kb=((evt.loaded*1)/1000).toFixed(1)
		
		 if(kb=="0.0"){
			kb=0.1;
		}
                     
        }, false);
 //Download progress
		xhr.addEventListener("progress", function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
			kb=0.1;
		}
                    
        }, false);
        return xhr;
    },
		error: function (jqXHR, textstatus, errorThrowm) {
	        verCerrarEfectoCargando("");
            manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
            ver_vetana_informativa("SE HA PRODUCTIDO UN ERROR");
		},
		success: function (responseText) {
			Respuesta = responseText;
			console.log(Respuesta);
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta == "exito") {
					document.getElementById('divMensajesSelectDictamenInterConsulta').innerHTML= datos["3"];
				} else {
                    throw new Error("Error producido en eliminarMencionMensaje de JavaScript.");
                }
			} catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
                var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			} finally {
                verCerrarEfectoCargando("");
            }
		}
	});
}

function obtenerDatosInterConsulta(elemento) {
    // Control de busqueda
    marcarFilaInterConsultaSeleccionada(elemento);
    controldebusquedadInformeInterConsulta= false;
    document.getElementById("divProgressInformeInterConsulta").style.width="0%";
    switch (ventanaAnterior[ventanaAnterior.length - 1]) {
        case 'divAbmGastosFijos':
            cod_interConsulta= $(elemento).children('#td_id').html();
            cod_ventaFKConsulta= $(elemento).children('#td_datos_4').html();
            cod_clienteConsulta= $(elemento).children('#td_datos_7').html();
            document.getElementById('inptAbmInterConsultaGastoFijo').value= $(elemento).children('#td_datos_10').html();
            verCerrarVentanaListadoInterConsulta(false);
            break;
        case 'divAbmGastos':
            cod_ventaFKConsulta= "";
            cod_clienteConsulta= "";
            cod_interConsulta= $(elemento).children('#td_id').html();
            document.getElementById('inptAbmInterConsultaGasto').value= $(elemento).children('#td_datos_10').html();
            if (typeof buscarProyectosVistaSelecc == "function") {
                buscarProyectosVistaSelecc();
            }
            verCerrarVentanaListadoInterConsulta(false);
            //ventanaAnterior.pop();
            document.getElementById('divAbmGastos').style.display= "";
            //verCerrarVentanaDetalleInterConsulta(true, 'divAbmGastos');
            //buscarInterConsultasYContenido(cod_interConsulta);
            break;
        case 'divAbmGasto1':
            cod_ventaFKConsulta= "";
            cod_clienteConsulta= "";
            document.getElementById('inptAbmInterConsultaGasto').value= $(elemento).children('#td_datos_10').html();
            buscarInterConsultasYContenido(cod_interConsulta);
            break;
        case 'divInformeDictamen':
            cod_ventaFKConsulta= "";
            cod_clienteConsulta= "";
            cod_interConsulta= $(elemento).children('#td_datos_22').html();
            document.getElementById('inptAbmInterConsultaGasto').value= $(elemento).children('#td_datos_10').html();
            ventanaAnterior.pop();
            minimizarInformeDictamen();
            verCerrarVentanaDetalleInterConsulta(true, 'divInformeDictamen');
            buscarInterConsultasYContenido(cod_interConsulta);
            break;
        case 'divAbmDetallesInterConsulta':
            cod_ventaFKConsulta= $(elemento).children('#td_datos_4').html();
            cod_clienteConsulta= $(elemento).children('#td_datos_7').html();
            buscarInterConsultasYContenido($(elemento).children('#td_id').html(), elemento);
            break;
        case 'divListadoInterConsulta':
            cancelarInformeInterConsulta();
            limpiarCamposDetallesInterConsulta();
            buscarInterConsultasYContenido($(elemento).children('#td_id').html(), elemento);
            limpiarcamposMensaje();
            verCerrarVentanaDetalleInterConsulta(true, 'divListadoInterConsulta');
            break;
        case 'divAbmInterConsulta':
            document.getElementById('inptCodInterc1AbmInterConsulta').value= $(elemento).children('#td_id').html();
            document.getElementById('inptInterc1AbmInterConsulta').value= $(elemento).children('#td_datos_10').html();
            verCerrarVentanaListadoInterConsulta(false);
            break;
        default:
            cod_ventaFKConsulta= $(elemento).children('#td_datos_4').html();
            cod_clienteConsulta= $(elemento).children('#td_datos_7').html();
            verCerrarVentanaListadoInterConsulta(false);
            verCerrarVentanaDetalleInterConsulta(true, 'divListadoInterConsulta');
            limpiarCamposDetallesInterConsulta();
            buscarInterConsultasYContenido($(elemento).children('#td_id').html(), elemento);
            limpiarcamposMensaje();
            break;
    }

    // Datos para editar
    document.getElementById('inptNombreClienteAbmInterConsulta').value= $(elemento).children('#td_datos_5').html();
    cod_clienteConsulta= $(elemento).children('#td_datos_7').html();
    document.getElementById('inptAsuntoAbmInterConsulta').value= $(elemento).children('#td_datos_10').html();
    document.getElementById('inptLocalAbmInterConsulta').value= $(elemento).children('#td_datos_11').html();
    var tipoOriginal= $(elemento).children('#td_datos_6').html() || "";
    prepararCategoriaAbmDesdeTipo(tipoOriginal);
    document.getElementById('inptEstadoAbmInterConsulta').value= $(elemento).children('#td_datos_2').html();
    document.getElementById('inptMontoLimiteAbmInterConsulta').value= $(elemento).children('#td_datos_15').html();
    document.getElementById('inptObservacionAbmInterConsulta').value= $(elemento).children('#td_datos_16').html();
}

function obtenerDetallesInterConsulta(origen) {
    const elemento= document.getElementById('contenedorEncabezadoInterConsulta').parentElement;

    cod_interConsulta= elemento.querySelector('#td_datos_34')?.textContent.trim();
    cod_ventaFKConsulta= elemento.querySelector('#td_datos_35')?.textContent.trim();
    cod_clienteConsulta= elemento.querySelector('#td_datos_39')?.textContent.trim();
    
    switch (origen) {
        case 'interConsulta':
            document.getElementById('inptNombreClienteAbmInterConsulta').value= elemento.querySelector('#td_datos_37')?.textContent.trim();
            document.getElementById('inptAsuntoAbmInterConsulta').value= elemento.querySelector('#td_datos_31')?.textContent.trim();
            prepararCategoriaAbmDesdeTipo(elemento.querySelector('#td_datos_33')?.textContent.trim() || "");
            document.getElementById('inptEstadoAbmInterConsulta').value= elemento.querySelector('#td_datos_32')?.textContent.trim();
            document.getElementById('inptLocalAbmInterConsulta').value= elemento.querySelector('#td_datos_38')?.textContent.trim();
            document.getElementById('inptMontoLimiteAbmInterConsulta').value= elemento.querySelector('#td_datos_41')?.textContent.trim();
            separadordemiles(document.getElementById('inptMontoLimiteAbmInterConsulta'));
            document.getElementById('inptObservacionAbmInterConsulta').value= elemento.querySelector('#td_datos_43')?.textContent.trim();
            verCerrarVentanaInterConsulta(true, 'divAbmDetallesInterConsulta');
            buscarInterConsultasAsociadasPaciente(cod_clienteConsulta);
            break;
    }
}

function buscarInterConsultasAsociadasPaciente(cod_cliente) {
    var clienteSolicitado= String(cod_cliente || "").trim();
    var hiloSolicitado= String(cod_interConsulta || "").trim();
    var secuenciaSolicitud= ++secuenciaInterConsultasAsociadas;
    if (solicitudInterConsultasAsociadasActiva && solicitudInterConsultasAsociadasActiva.readyState !== 4) {
        solicitudInterConsultasAsociadasActiva.abort();
    }
    if (!clienteSolicitado || !hiloSolicitado) {
        solicitudInterConsultasAsociadasActiva= null;
        return false;
    }
    function solicitudInterConsultasAsociadasVigente() {
        return secuenciaSolicitud === secuenciaInterConsultasAsociadas
            && String(cod_interConsulta || "").trim() === hiloSolicitado
            && String(cod_clienteConsulta || "").trim() === clienteSolicitado;
    }

    obtener_datos_user();
    let datos= new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", 'buscarVistaAsociadoPaciente');
    datos.append("cod_cliente", clienteSolicitado);
    datos.append("cod_interConsulta", hiloSolicitado);

    const contenedorAsociados= document.getElementById('list_detalles_interconsultas_asoc');
    if (contenedorAsociados) {
        contenedorAsociados.innerHTML= '<div class="interconsulta-flow-state">Cargando hilos relacionados...</div>';
    }
    var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmInterConsulta.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		 xhr: function () {
        var xhr = new window.XMLHttpRequest();
        //Uload progress
        xhr.upload.addEventListener("progress" ,function (evt) {
         var kb=((evt.loaded*1)/1000).toFixed(1)
		
		 if(kb=="0.0"){
			kb=0.1;
		}
                     
        }, false);
 //Download progress
		xhr.addEventListener("progress", function (evt) {
        var kb=((evt.loaded*1)/1000).toFixed(1)
		if(kb=="0.0"){
			kb=0.1;
		}
                    
        }, false);
        return xhr;
    },
		error: function (jqXHR, textstatus, errorThrowm) {
            if (textstatus == "abort") {
                return;
            }
            if (!solicitudInterConsultasAsociadasVigente()) {
                return;
            }
            manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
            if (contenedorAsociados) {
                contenedorAsociados.innerHTML= '<div class="interconsulta-flow-state interconsulta-flow-state--error">No se pudieron cargar los hilos relacionados.</div>';
            }
		},
		success: function (responseText) {
			if (!solicitudInterConsultasAsociadasVigente()) {
				return;
			}
			try {
				var datos = $.parseJSON(responseText);
				if (datos["1"] == "exito") {
                    if (datos["2"]) {
                        document.getElementById('divListDetallesInterconsultasAsoc').style.display= "";
                        document.getElementById('list_abm_interConsulta_asoc').innerHTML= datos["2"];
                        document.getElementById('list_detalles_interconsultas_asoc').innerHTML= datos["2"];
                    }
                } else {
                    throw new Error("El servidor no pudo cargar los hilos relacionados.");
                }
			} catch (error) {
                var tipoErrorAsociados= error && error.name ? String(error.name).replace(/[^A-Za-z]/g, "").slice(0, 40) : "Error";
                var titulo="Error JavaScript al cargar hilos relacionados ("+tipoErrorAsociados+").";
				GuardarArchivosLog(titulo)
                if (contenedorAsociados) {
                    contenedorAsociados.innerHTML= '<div class="interconsulta-flow-state interconsulta-flow-state--error">No se pudieron cargar los hilos relacionados.</div>';
                }
			}
		},
        complete: function() {
            if (solicitudInterConsultasAsociadasActiva === OpAjax) {
                solicitudInterConsultasAsociadasActiva= null;
            }
        }
	});
    solicitudInterConsultasAsociadasActiva= OpAjax;
    return OpAjax;
}

function cancelarInformeInterConsulta() {
    busquedaInterConsultaCancelada= true;
	controldebusquedadInformeInterConsulta=false
	secuenciaListadoInterConsulta++;
    abortarSolicitudHilosInterConsulta(solicitudListadoInterConsultaActiva);
    abortarSolicitudHilosInterConsulta(solicitudEnriquecimientoListadoInterConsultaActiva);
    solicitudListadoInterConsultaActiva= null;
    solicitudEnriquecimientoListadoInterConsultaActiva= null;
    var progreso= document.getElementById("divProgressInformeInterConsulta");
    if (progreso) {
        progreso.style.backgroundColor='#ff5722';
    }
    actualizarBotonCargarMasHilosInterConsulta();
}

function marcarFilaInterConsultaSeleccionada(elemento) {
    const filaTabla= elemento && elemento.closest ? elemento.closest(".interconsulta-thread-row") : null;
    const listado= document.getElementById("table_frm_VistaInterConsulta");
    if (!filaTabla || !listado || !listado.contains(filaTabla)) {
        return;
    }

    listado.querySelectorAll(".interconsulta-thread-row--selected").forEach(function(fila) {
        fila.classList.remove("interconsulta-thread-row--selected");
    });
    filaTabla.classList.add("interconsulta-thread-row--selected");
}

function limpiarCamposDetallesInterConsulta() {
    if (solicitudContextoSeguimientoInterConsultaActiva && solicitudContextoSeguimientoInterConsultaActiva.readyState !== 4) {
        solicitudContextoSeguimientoInterConsultaActiva.abort();
    }
    solicitudContextoSeguimientoInterConsultaActiva= null;
    cancelarSolicitudContextoMensajeInterConsulta();
    cancelarRespuestaCitadaInterConsulta();
    cerrarPanelSeguimientoProgramadoInterConsulta(false);
    reiniciarFormularioSeguimientoInterConsulta();
    plantillasSeguimientoInterConsulta= [];
    plantillasAdministracionSeguimientoInterConsulta= [];
    responsablesSeguimientoInterConsulta= [];
    var panelSeguimiento= document.getElementById("panelProgramarSeguimientoInterConsulta");
    if (panelSeguimiento) {
        panelSeguimiento.removeAttribute("data-cod-hilo");
        panelSeguimiento.removeAttribute("data-contexto-hilo");
    }
    document.getElementById('table_abm_InterConsulta').innerHTML= "";
    document.getElementById('divListDetallesInterconsultasAsoc').style.display= "none";
    document.getElementById('divObservacionDetallesInterconsultas').style.display= "none";
}

function limpiarcamposInterconsulta() {
    cod_ventaFKConsulta= "";
    cod_interConsulta= "";
    cod_clienteConsulta= "";
    categoriaOriginalAbmInterConsulta= "";
    tipoOriginalAbmInterConsulta= "";
    
    document.getElementById('inptAsuntoAbmInterConsulta').value= "";
    document.getElementById('inptNombreClienteAbmInterConsulta').value= "";
    document.getElementById('inptEstadoAbmInterConsulta').value= "pendiente";
    var categoriaNueva= obtenerCategoriaActivaInterConsulta();
    var selectCategoria= document.getElementById("inptCategoriaAbmInterConsulta");
    if (selectCategoria) {
        selectCategoria.value= categoriaNueva;
    }
    actualizarOpcionesSubtipoAbmInterConsulta(categoriaNueva);
    document.getElementById('inptMontoLimiteAbmInterConsulta').value= "";
    document.getElementById('inptInterc1AbmInterConsulta').value= "";
    document.getElementById('inptCodInterc1AbmInterConsulta').value= "";
    document.getElementById('inptObservacionAbmInterConsulta').value= "";

    document.getElementById('list_abm_interConsulta_asoc').innerHTML= "";
    document.getElementById('list_detalles_interconsultas_asoc').innerHTML= "";

    // Campos de fusion
    document.getElementById('inptInterc1AbmInterConsulta').value= "";
    document.getElementById('inptCodInterc1AbmInterConsulta').value= "";
}

var ventanaAnterior= [];
function ajustarAltoDetalleInterConsulta() {
    var detalle= document.getElementById("divAbmDetallesInterConsulta");
    if (!detalle) {
        return;
    }

    var viewport= window.visualViewport;
    if (viewport) {
        detalle.style.setProperty("--interconsulta-viewport-height", Math.round(viewport.height) + "px");
        detalle.style.setProperty("--interconsulta-viewport-offset-top", Math.round(viewport.offsetTop || 0) + "px");
        return;
    }

    detalle.style.setProperty("--interconsulta-viewport-height", window.innerHeight + "px");
    detalle.style.setProperty("--interconsulta-viewport-offset-top", "0px");
}

if (window.visualViewport) {
    window.visualViewport.addEventListener("resize", ajustarAltoDetalleInterConsulta);
    window.visualViewport.addEventListener("scroll", ajustarAltoDetalleInterConsulta);
} else {
    window.addEventListener("resize", ajustarAltoDetalleInterConsulta);
}

function verCerrarVentanaDetalleInterConsulta(mostrar, anterior= '') {
    if (mostrar) {
        verCerrarVentanaListadoInterConsulta(false);
        ajustarAltoDetalleInterConsulta();
        document.getElementById("divAbmDetallesInterConsulta").style.display= "";

        if (!anterior && ventanaAnterior.length > 0) {
            const ultimoElemento= ventanaAnterior[ventanaAnterior.length - 1]
            document.getElementById(ultimoElemento).style.display= 'none';
            ventanaAnterior.pop();
        }
    } else {
        cerrarPanelSeguimientoProgramadoInterConsulta(false);
        cerrarSelectorVentasSeguimientoPaciente();
        document.getElementById("divAbmDetallesInterConsulta").style.display= "none";
        document.getElementById("collapseExtracto").className= "extracto-floating-panel collapse";
        if (ventanaAnterior.length > 0) {
            switch (ventanaAnterior[ventanaAnterior.length - 1]) {
                case 'divListadoInterConsulta':
                    buscarPacientesConInterConsultas();
                    //verCerrarVentanaListadoInterConsulta(true);
                    break;
            }
            
            const ultimoElemento= ventanaAnterior[ventanaAnterior.length - 1]
            document.getElementById(ultimoElemento).style.display= '';
            ventanaAnterior.pop();
        }
    }

    // Guarda la ventanaAnterior
    if (anterior) {
        ventanaAnterior.push(anterior);
    }
}

function verCerrarVentanaListadoInterConsulta(mostrar, anterior= '') {
    if (mostrar) {
        // Comprueba si estaba minimizado
        if (document.getElementById('divMinimizadoInterConsulta').style.display == "") {
            document.getElementById('divMinimizadoInterConsulta').style.display="none";
        }

        // Obtiene tambien el listado de usuario
        if (typeof cargarUsuariosMencionesInterConsulta == "function") {
            cargarUsuariosMencionesInterConsulta();
        }
        buscarabmusuario2('', '', '', 'Activo', '');
        sincronizarOpcionesRapidasInterConsulta();
        aplicarLocalUsuarioInterConsulta(false);
        buscarPacientesConInterConsultas();

        document.getElementById("divListadoInterConsulta").style.display= "";
        inicializarControlesInterConsulta();

        if (anterior) {
            document.getElementById(anterior).style.display= "none";
        }

        switch (anterior) {
            case 'divAbmInterConsulta':
                document.getElementById('divAbmDetallesInterConsulta').style.display= 'none';
                break;
        }
    } else {
        cerrarSelectorVentasSeguimientoPaciente();
        document.getElementById("divListadoInterConsulta").style.display= "none";
        
        if (ventanaAnterior.length > 0) {
            const ultimoElemento= ventanaAnterior[ventanaAnterior.length - 1]
            document.getElementById(ultimoElemento).style.display= '';
            ventanaAnterior.pop();
        }
    }

    // Guarda la ventanaAnterior
    if (anterior) {
        ventanaAnterior.push(anterior);
    }
}

function verCerrarVentanaInterConsulta(mostrar, anterior= '') {
    if (mostrar) {
        document.getElementById('divAbmInterConsulta').style.display= "";
    } else {
        cerrarSelectorVentasSeguimientoPaciente();
        document.getElementById('divAbmInterConsulta').style.display= "none";

        switch (anterior) {
            case 'divListadoInterConsulta':
			    buscarPacientesConInterConsultas2("", "", "", "", "", "", "", userid, 0, true, "");
                break
            case 'divAbmDetallesInterConsulta':
                buscarPacientesConInterConsultas();
                break;
        }

        if (ventanaAnterior.length > 0) {
            const ultimoElemento= ventanaAnterior[ventanaAnterior.length - 1]
            document.getElementById(ultimoElemento).style.display= '';
            ventanaAnterior.pop();
        }
    }

    // Guarda la ventanaAnterior
    if (anterior) {
        ventanaAnterior.push(anterior);
    }
}

function minimizarabmInterConsulta() {
    document.getElementById('divAbmInterConsulta').style.display="none";
    document.getElementById('divMinimizadoInterConsulta').style.display="";
}

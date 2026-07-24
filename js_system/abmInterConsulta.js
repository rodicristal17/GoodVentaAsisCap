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
var solicitudCargaMensajeCitadoInterConsultaActiva= null;
var secuenciaCargaMensajeCitadoInterConsulta= 0;
var solicitudAlertasSeguimientoInterConsultaActiva= null;
var secuenciaAlertasSeguimientoInterConsulta= 0;
var firmaAlertasSeguimientoInterConsulta= "";
var temporizadorAlertasSeguimientoInterConsulta= null;
var intervaloAlertasSeguimientoInterConsultaMs= 120000;
var solicitudResumenHilosInterConsultaActiva= null;
var temporizadorResumenHilosInterConsulta= null;
var intervaloResumenHilosInterConsultaMs= 120000;
var manejadorVisibilidadResumenHilosInterConsultaInicializado= false;
var temporizadorCuentaRegresivaSeguimientoInterConsulta= null;
var avisosSeguimientoMostradosInterConsulta= {};
var manejadorTimelineSeguimientoInterConsultaInicializado= false;
var manejadorDialogoSeguimientoInterConsultaInicializado= false;
var elementoFocoAnteriorSeguimientoInterConsulta= null;
var codInterConsultaContextoSeguimiento= "";
var idSeguimientoAlertaPendienteInterConsulta= 0;
var categoriaActivaInterConsulta= "pagos_egresos";
var categoriaOriginalAbmInterConsulta= "";
var tipoOriginalAbmInterConsulta= "";
var hiloArrastradoInterConsulta= null;
var hiloReclasificacionInterConsulta= null;
var elementoFocoReclasificacionInterConsulta= null;
var categoriasHilosInterConsulta= {
    pagos_egresos: {
        nombre: "Pagos y Egresos",
        subtipos: [
            {valor: "pagos", texto: "Pagos"},
            {valor: "compras", texto: "Compras"},
            {valor: "egresos", texto: "Egresos"}
        ],
        tiposNormalizados: ["pagos", "pago", "compras", "compra", "egresos", "egreso", "colaborador", "rrhh"]
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

function guardarFiltrosBusquedaInterConsulta(cod_interC, asunto, nombre_responsable, nombre_cliente, estado, tipo, cod_localFK, cod_usuarioFK, ocultar_inactivos, usuario_vinculado, busqueda_global, fecha_desde, fecha_hasta, categoria_principal, filtro_menciones) {
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
        categoria_principal: categoria_principal || obtenerCategoriaActivaInterConsulta(),
        filtro_menciones: filtro_menciones || ""
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
    if (fechaDesde == "" && fechaHasta == "") {
        campoRapido.value= "";
        textoDia.textContent= "Todas";
        textoFecha.textContent= "Todas las fechas";
        return;
    }

    if ((fechaDesde != "" || fechaHasta != "") && fechaDesde != fechaHasta) {
        textoDia.textContent= "Rango";
        textoFecha.textContent= (fechaDesde != "" ? formatearFechaCortaInterConsulta(fechaDesde) : "--/--/----")
            + " - "
            + (fechaHasta != "" ? formatearFechaCortaInterConsulta(fechaHasta) : "--/--/----");
        return;
    }

    var fecha= campoRapido.value || fechaDesde || fechaHasta;
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
        actualizarVistaFechaRapidaInterConsulta();
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
    if (!forzar && localUsuarioInicializadoInterConsulta) {
        return false;
    }
    // La vista normal incluye todos los locales autorizados. El usuario puede
    // acotarla manualmente con el filtro de local.
    localUsuarioInicializadoInterConsulta= true;
    return true;
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

    var menciones= textoOpcionSeleccionadaInterConsulta("inptFiltroMencionesInterConsulta");
    if (menciones != "") {
        filtros.push("Menciones: " + menciones);
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
        asignarValorCampoInterConsulta("inptFiltroRapidoFechaInterConsulta", "");
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
        "inptFiltroMencionesInterConsulta",
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
    actualizarVistaFechaRapidaInterConsulta();

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
var hiloSelectorSeguimientoInterConsulta= "";

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
                hiloSelectorSeguimientoInterConsulta= codInterConsulta;
                if (ventas.length > 0) {
                    mostrarSelectorVentasSeguimientoPaciente(paciente, ventas);
                } else {
                    ver_vetana_informativa("Sin ventas vinculadas", "No se encontraron ventas o cuotas asociadas a este hilo.", "info");
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
                    +"<h3 id='tituloSelectorVentasSeguimiento'>Ventas y detalle de cuotas</h3>"
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
            html += "<article class='interconsulta-sale-item " + clase + "' data-sale-index='"+i+"'>"
                +"<button type='button' class='interconsulta-sale-option' aria-expanded='false' aria-controls='detalleCuotasVentaInterConsulta-"+i+"' onclick='alternarDetalleCuotasSeguimientoInterConsulta("+i+", this)'>"
                    +"<span class='interconsulta-sale-option__main'>"
                        +"<strong>Venta #" + escaparHtmlSeguimientoHilo(venta.cod_venta || "") + "</strong>"
                        +"<small>" + escaparHtmlSeguimientoHilo(venta.fecha_venta ? formatearFechaSeguimientoHilo(venta.fecha_venta) : "") + " - " + escaparHtmlSeguimientoHilo(venta.nombre_local || "") + "</small>"
                    +"</span>"
                    +"<span class='interconsulta-sale-option__status'>"
                        +"<b>" + escaparHtmlSeguimientoHilo(venta.estado || "Sin cuotas pendientes") + "</b>"
                        +"<small>" + escaparHtmlSeguimientoHilo(venta.saldo_pendiente_formato || "") + "</small>"
                    +"</span>"
                    +"<i class='fa-solid fa-chevron-down' aria-hidden='true'></i>"
                +"</button>"
                +"<div class='interconsulta-sale-installments' id='detalleCuotasVentaInterConsulta-"+i+"' hidden>"
                    +"<div class='interconsulta-sale-selector__empty'>Abra la venta para consultar sus cuotas.</div>"
                +"</div>"
                +"<footer><button type='button' onclick='seleccionarVentaSeguimientoDesdeSelector("+i+")'>Ver historial completo</button></footer>"
            +"</article>";
        }
        body.innerHTML= html || "<div class='interconsulta-sale-selector__empty'>No se encontraron ventas para seleccionar.</div>";
    }
    modal.style.display= "flex";
    if (ventasSelectorSeguimientoInterConsulta.length == 1) {
        setTimeout(function() {
            var botonUnico= modal.querySelector(".interconsulta-sale-option");
            alternarDetalleCuotasSeguimientoInterConsulta(0, botonUnico);
        }, 0);
    }
}

function cerrarSelectorVentasSeguimientoPaciente() {
    var modal= document.getElementById("modalSelectorVentasSeguimientoInterConsulta");
    if (modal) {
        modal.style.display= "none";
    }
    ventasSelectorSeguimientoInterConsulta= [];
    pacienteSelectorSeguimientoInterConsulta= {};
    hiloSelectorSeguimientoInterConsulta= "";
}

function datosFilaHiloInterConsulta(fila) {
    if (!fila) { return null; }
    var celdaCodigo= fila.querySelector("#td_id");
    var celdaTipo= fila.querySelector("#td_datos_6");
    var celdaAsunto= fila.querySelector("#td_datos_10");
    var codigo= celdaCodigo ? String(celdaCodigo.textContent || "").trim() : "";
    var tipo= celdaTipo ? String(celdaTipo.textContent || "").trim() : "";
    if (!codigo) { return null; }
    return {
        cod_interConsulta: codigo,
        tipo: tipo,
        categoria: obtenerCategoriaPrincipalHilo(tipo),
        asunto: celdaAsunto ? String(celdaAsunto.textContent || "").trim() : ("Hilo #" + codigo)
    };
}

function limpiarEstadoArrastreHilosInterConsulta() {
    document.querySelectorAll(".hilos-category-tab").forEach(function(tab) {
        tab.classList.remove("is-drop-target", "is-drop-over", "is-drop-disabled");
    });
    if (hiloArrastradoInterConsulta && hiloArrastradoInterConsulta.fila) {
        hiloArrastradoInterConsulta.fila.classList.remove("is-dragging");
    }
    hiloArrastradoInterConsulta= null;
}

function iniciarArrastreHiloInterConsulta(event, asa) {
    var fila= asa ? asa.closest(".interconsulta-thread-row") : null;
    var datos= datosFilaHiloInterConsulta(fila);
    if (!datos) {
        event.preventDefault();
        return;
    }
    hiloArrastradoInterConsulta= datos;
    hiloArrastradoInterConsulta.fila= fila;
    fila.classList.add("is-dragging");
    document.querySelectorAll(".hilos-category-tab").forEach(function(tab) {
        var categoria= tab.getAttribute("data-hilos-category") || "";
        tab.classList.add(categoria == datos.categoria ? "is-drop-disabled" : "is-drop-target");
    });
    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed= "move";
        event.dataTransfer.setData("text/plain", datos.cod_interConsulta);
    }
}

function prepararDestinoArrastreHiloInterConsulta(event, tab) {
    if (!hiloArrastradoInterConsulta || !tab) { return; }
    var categoria= tab.getAttribute("data-hilos-category") || "";
    if (!categoriasHilosInterConsulta[categoria] || categoria == hiloArrastradoInterConsulta.categoria) { return; }
    event.preventDefault();
    if (event.dataTransfer) { event.dataTransfer.dropEffect= "move"; }
    tab.classList.add("is-drop-over");
}

function soltarHiloEnCategoriaInterConsulta(event, tab) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    if (!hiloArrastradoInterConsulta || !tab) {
        limpiarEstadoArrastreHilosInterConsulta();
        return;
    }
    var datos= hiloArrastradoInterConsulta;
    var categoria= tab.getAttribute("data-hilos-category") || "";
    limpiarEstadoArrastreHilosInterConsulta();
    if (!categoriasHilosInterConsulta[categoria] || categoria == datos.categoria) { return; }
    abrirDialogoReclasificarHiloInterConsulta(datos, categoria, tab);
}

function inicializarArrastreHilosInterConsulta() {
    document.querySelectorAll("#table_frm_VistaInterConsulta .interconsulta-thread-row").forEach(function(fila) {
        if (fila.getAttribute("data-drag-inicializado") == "1") { return; }
        var celda= fila.querySelector("#td_id");
        if (!celda) { return; }
        var asa= document.createElement("button");
        asa.type= "button";
        asa.className= "hilos-thread-drag-handle";
        asa.draggable= true;
        asa.title= "Arrastre a una pestaña o presione para cambiar la categoría";
        asa.setAttribute("aria-label", asa.title);
        asa.innerHTML= '<i class="fa-solid fa-grip-vertical" aria-hidden="true"></i>';
        asa.addEventListener("click", function(event) {
            event.preventDefault();
            event.stopPropagation();
            var datos= datosFilaHiloInterConsulta(fila);
            if (datos) { abrirDialogoReclasificarHiloInterConsulta(datos, "", asa); }
        });
        asa.addEventListener("dragstart", function(event) {
            event.stopPropagation();
            iniciarArrastreHiloInterConsulta(event, asa);
        });
        asa.addEventListener("dragend", limpiarEstadoArrastreHilosInterConsulta);
        celda.classList.add("hilos-thread-code-cell");
        celda.appendChild(asa);
        fila.setAttribute("data-drag-inicializado", "1");
    });

    document.querySelectorAll(".hilos-category-tab").forEach(function(tab) {
        if (tab.getAttribute("data-drop-inicializado") == "1") { return; }
        tab.addEventListener("dragover", function(event) { prepararDestinoArrastreHiloInterConsulta(event, tab); });
        tab.addEventListener("dragleave", function() { tab.classList.remove("is-drop-over"); });
        tab.addEventListener("drop", function(event) { soltarHiloEnCategoriaInterConsulta(event, tab); });
        tab.setAttribute("data-drop-inicializado", "1");
    });
}

function inicializarAccionesGestionProgramadaListadoInterConsulta() {
    document.querySelectorAll("#table_frm_VistaInterConsulta .interconsulta-management-pill--empty").forEach(function(elemento) {
        var fila= elemento.closest(".interconsulta-thread-row");
        var datos= datosFilaHiloInterConsulta(fila);
        if (!datos) { return; }
        if (!elemento.getAttribute("data-cod-interconsulta")) {
            elemento.setAttribute("data-cod-interconsulta", datos.cod_interConsulta);
        }
        elemento.setAttribute("title", "Programar una tarea interna sin abrir el hilo");
        if (elemento.tagName.toLowerCase() != "button") {
            elemento.setAttribute("role", "button");
            elemento.setAttribute("tabindex", "0");
        }
        if (elemento.getAttribute("data-followup-listener") == "1" || elemento.getAttribute("onclick")) { return; }
        var abrir= function(event) {
            if (event.type == "keydown" && !(event.key == "Enter" || event.key == " " || event.keyCode == 13 || event.keyCode == 32)) { return; }
            abrirSeguimientoProgramadoDesdeListado(elemento, event);
        };
        elemento.addEventListener("click", abrir);
        elemento.addEventListener("keydown", abrir);
        elemento.setAttribute("data-followup-listener", "1");
    });
}

function obtenerDialogoReclasificarHiloInterConsulta() {
    var dialogo= document.getElementById("dialogoReclasificarHiloInterConsulta");
    if (dialogo) { return dialogo; }
    dialogo= document.createElement("div");
    dialogo.id= "dialogoReclasificarHiloInterConsulta";
    dialogo.className= "interconsulta-reclassify-dialog";
    dialogo.hidden= true;
    dialogo.innerHTML= '<div class="interconsulta-reclassify-dialog__backdrop" onclick="cerrarDialogoReclasificarHiloInterConsulta()"></div>'
        +'<section class="interconsulta-reclassify-dialog__panel" role="dialog" aria-modal="true" aria-labelledby="tituloReclasificarHiloInterConsulta">'
            +'<header><div><small>Cambio trazable</small><h3 id="tituloReclasificarHiloInterConsulta">Cambiar categoría del hilo</h3><p id="resumenReclasificarHiloInterConsulta"></p></div>'
            +'<button type="button" onclick="cerrarDialogoReclasificarHiloInterConsulta()" aria-label="Cerrar">x</button></header>'
            +'<div class="interconsulta-reclassify-dialog__body">'
                +'<label>Categoría de destino<select id="categoriaReclasificarHiloInterConsulta" onchange="actualizarSubtiposReclasificarHiloInterConsulta()"></select></label>'
                +'<label>Subtipo<select id="tipoReclasificarHiloInterConsulta"></select></label>'
                +'<p><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Se conservarán paciente, venta, mensajes, tareas, citas y documentos.</p>'
            +'</div>'
            +'<footer><button type="button" class="is-secondary" onclick="cerrarDialogoReclasificarHiloInterConsulta()">Cancelar</button>'
            +'<button type="button" id="btnConfirmarReclasificarHiloInterConsulta" onclick="confirmarReclasificacionHiloInterConsulta()">Cambiar categoría</button></footer>'
        +'</section>';
    document.body.appendChild(dialogo);
    dialogo.addEventListener("keydown", manejarTecladoDialogoReclasificarHiloInterConsulta);
    return dialogo;
}

function manejarTecladoDialogoReclasificarHiloInterConsulta(event) {
    var dialogo= document.getElementById("dialogoReclasificarHiloInterConsulta");
    if (!dialogo || dialogo.hidden) { return; }
    if (event.key == "Escape" || event.keyCode == 27) {
        event.preventDefault();
        cerrarDialogoReclasificarHiloInterConsulta();
        return;
    }
    if (event.key != "Tab" && event.keyCode != 9) { return; }
    var controles= Array.prototype.slice.call(dialogo.querySelectorAll(
        'button:not([disabled]), select:not([disabled]), input:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
    )).filter(function(elemento) {
        return !elemento.hidden && elemento.offsetParent !== null;
    });
    if (!controles.length) {
        event.preventDefault();
        return;
    }
    var primero= controles[0];
    var ultimo= controles[controles.length - 1];
    if (event.shiftKey && document.activeElement === primero) {
        event.preventDefault();
        ultimo.focus();
    } else if (!event.shiftKey && document.activeElement === ultimo) {
        event.preventDefault();
        primero.focus();
    }
}

function actualizarSubtiposReclasificarHiloInterConsulta(valorPreferido) {
    var categoria= valorCampoInterConsulta("categoriaReclasificarHiloInterConsulta");
    var select= document.getElementById("tipoReclasificarHiloInterConsulta");
    if (!select || !categoriasHilosInterConsulta[categoria]) { return; }
    select.innerHTML= "";
    categoriasHilosInterConsulta[categoria].subtipos.forEach(function(subtipo) {
        var opcion= document.createElement("option");
        opcion.value= subtipo.valor;
        opcion.textContent= subtipo.texto;
        select.appendChild(opcion);
    });
    if (valorPreferido) { select.value= valorPreferido; }
}

function abrirDialogoReclasificarHiloInterConsulta(datos, categoriaDestino, origen) {
    if (!datos || !datos.cod_interConsulta) { return; }
    if (typeof controlacceso == "function" && controlacceso("EDITARINTERCONSULTA", "accion") == false) { return; }
    var dialogo= obtenerDialogoReclasificarHiloInterConsulta();
    hiloReclasificacionInterConsulta= datos;
    elementoFocoReclasificacionInterConsulta= origen || document.activeElement;
    var categoria= categoriasHilosInterConsulta[categoriaDestino] ? categoriaDestino : (datos.categoria || obtenerCategoriaActivaInterConsulta());
    var selectCategoria= document.getElementById("categoriaReclasificarHiloInterConsulta");
    selectCategoria.innerHTML= "";
    Object.keys(categoriasHilosInterConsulta).forEach(function(clave) {
        var opcion= document.createElement("option");
        opcion.value= clave;
        opcion.textContent= categoriasHilosInterConsulta[clave].nombre;
        selectCategoria.appendChild(opcion);
    });
    selectCategoria.value= categoria;
    actualizarSubtiposReclasificarHiloInterConsulta(categoria == datos.categoria ? datos.tipo : "");
    var resumen= document.getElementById("resumenReclasificarHiloInterConsulta");
    if (resumen) { resumen.textContent= "Hilo #" + datos.cod_interConsulta + " · " + (datos.asunto || "Sin asunto"); }
    dialogo.hidden= false;
    document.body.classList.add("interconsulta-reclassify-dialog-open");
    setTimeout(function() { selectCategoria.focus(); }, 0);
}

function cerrarDialogoReclasificarHiloInterConsulta() {
    var dialogo= document.getElementById("dialogoReclasificarHiloInterConsulta");
    if (dialogo) { dialogo.hidden= true; }
    document.body.classList.remove("interconsulta-reclassify-dialog-open");
    hiloReclasificacionInterConsulta= null;
    var foco= elementoFocoReclasificacionInterConsulta;
    elementoFocoReclasificacionInterConsulta= null;
    if (foco && document.documentElement.contains(foco) && typeof foco.focus == "function") {
        try { foco.focus({preventScroll: true}); } catch (error) { foco.focus(); }
    }
}

function confirmarReclasificacionHiloInterConsulta() {
    if (!hiloReclasificacionInterConsulta) { return; }
    var categoria= valorCampoInterConsulta("categoriaReclasificarHiloInterConsulta");
    var tipo= valorCampoInterConsulta("tipoReclasificarHiloInterConsulta");
    if (!categoriasHilosInterConsulta[categoria] || !tipo) { return; }
    if (categoria == hiloReclasificacionInterConsulta.categoria && normalizarTextoHiloInterConsulta(tipo) == normalizarTextoHiloInterConsulta(hiloReclasificacionInterConsulta.tipo)) {
        cerrarDialogoReclasificarHiloInterConsulta();
        return;
    }
    reclasificarHiloInterConsulta(hiloReclasificacionInterConsulta.cod_interConsulta, categoria, tipo, {
        boton: document.getElementById("btnConfirmarReclasificarHiloInterConsulta"),
        alCompletar: cerrarDialogoReclasificarHiloInterConsulta
    });
}

function reclasificarHiloInterConsulta(codHilo, categoria, tipo, opciones) {
    opciones= opciones || {};
    if (typeof controlacceso == "function" && controlacceso("EDITARINTERCONSULTA", "accion") == false) { return; }
    obtener_datos_user();
    var datos= new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "reclasificarHiloInterConsulta");
    datos.append("cod_interConsulta", codHilo);
    datos.append("categoria_principal", categoria);
    datos.append("tipo", tipo);
    var boton= opciones.boton || null;
    if (boton) { boton.disabled= true; boton.classList.add("is-saving"); }
    $.ajax({
        data: datos,
        url: "../php_system/abmInterConsulta.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        success: function(responseText) {
            try {
                var respuesta= typeof responseText == "string" ? $.parseJSON(responseText) : responseText;
                if (respuesta["1"] != "exito") {
                    throw new Error(mensajeRespuestaSeguimientoInterConsulta(respuesta, "No se pudo cambiar la categoría."));
                }
                if (typeof opciones.alCompletar == "function") { opciones.alCompletar(respuesta["2"] || {}); }
                ver_vetana_informativa("Categoría actualizada", "El hilo fue reclasificado y se conservó todo su historial.", "info");
                if (String(cod_interConsulta || "") == String(codHilo)) {
                    if (categoria != obtenerCategoriaActivaInterConsulta()) {
                        cerrarDetallePorCambioCategoriaHilo();
                    } else {
                        buscarInterConsultasYContenido(codHilo);
                    }
                }
                buscarPacientesConInterConsultas();
            } catch (error) {
                ver_vetana_informativa("No se pudo reclasificar", error.message || "Revise los permisos e intente nuevamente.", "advertencia");
            }
        },
        error: function(jqXHR, textstatus) {
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmInterConsulta");
            ver_vetana_informativa("No se pudo reclasificar", "La conexión falló. El hilo no fue modificado.", "error");
        },
        complete: function() {
            if (boton && document.documentElement.contains(boton)) { boton.disabled= false; boton.classList.remove("is-saving"); }
        }
    });
}

function formatoMontoCuotaInterConsulta(valor) {
    var numero= Number(valor || 0);
    try { return new Intl.NumberFormat("es-PY", {maximumFractionDigits: 0}).format(numero) + " Gs."; }
    catch (error) { return String(numero) + " Gs."; }
}

function alternarDetalleCuotasSeguimientoInterConsulta(indice, boton) {
    var panel= document.getElementById("detalleCuotasVentaInterConsulta-" + indice);
    var venta= ventasSelectorSeguimientoInterConsulta[indice];
    if (!panel || !venta) { return; }
    var abrir= panel.hidden;
    panel.hidden= !abrir;
    if (boton) {
        boton.setAttribute("aria-expanded", abrir ? "true" : "false");
        var icono= boton.querySelector(".fa-chevron-down, .fa-chevron-up");
        if (icono) {
            icono.classList.toggle("fa-chevron-down", !abrir);
            icono.classList.toggle("fa-chevron-up", abrir);
        }
    }
    if (abrir && panel.getAttribute("data-cargado") != "1" && panel.getAttribute("data-cargando") != "1") {
        cargarDetalleCuotasSeguimientoInterConsulta(indice, panel);
    }
}

function cargarDetalleCuotasSeguimientoInterConsulta(indice, panel) {
    var venta= ventasSelectorSeguimientoInterConsulta[indice];
    var hilo= String(hiloSelectorSeguimientoInterConsulta || "");
    if (!venta || !hilo || !panel) { return; }
    panel.setAttribute("data-cargando", "1");
    panel.innerHTML= '<div class="interconsulta-sale-selector__empty">Cargando cuotas...</div>';
    obtener_datos_user();
    var datos= new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "buscarDetalleCuotasInterConsulta");
    datos.append("cod_interConsulta", hilo);
    datos.append("cod_venta", venta.cod_venta || "");
    $.ajax({
        data: datos,
        url: "../php_system/abmInterConsulta.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        success: function(responseText) {
            try {
                var respuesta= typeof responseText == "string" ? $.parseJSON(responseText) : responseText;
                if (respuesta["1"] != "exito") {
                    throw new Error(mensajeRespuestaSeguimientoInterConsulta(respuesta, "No se pudieron consultar las cuotas."));
                }
                var ventas= respuesta["2"] && Array.isArray(respuesta["2"].ventas) ? respuesta["2"].ventas : [];
                var detalle= ventas.length ? ventas[0] : {};
                var cuotas= Array.isArray(detalle.cuotas) ? detalle.cuotas : [];
                if (!cuotas.length) {
                    panel.innerHTML= '<div class="interconsulta-sale-selector__empty">Esta venta no tiene cuotas para mostrar.</div>';
                } else {
                    panel.innerHTML= '<div class="interconsulta-installments-table" role="table" aria-label="Detalle de cuotas">'
                        +'<div class="interconsulta-installments-table__head" role="row"><span>Cuota</span><span>Vencimiento</span><span>Pagado</span><span>Saldo</span><span>Estado</span></div>'
                        +cuotas.map(function(cuota) {
                            var numero= cuota.nro_cuota || cuota.numero_cuota || cuota.id_credito || "-";
                            var pagado= Number(cuota.pagado_capital || cuota.capital_pagado || 0) + Number(cuota.pagado_interes || cuota.interes_pagado || 0);
                            var saldo= cuota.saldo !== undefined ? cuota.saldo : (cuota.saldo_pendiente || 0);
                            var estado= cuota.estado || (Number(cuota.dias_mora || 0) > 0 ? "Vencida" : "Pendiente");
                            var estadoNormalizado= String(estado).trim().toLowerCase();
                            var clase= estadoNormalizado == "pagada"
                                ? "is-paid"
                                : (Number(cuota.dias_mora || 0) > 0 || estadoNormalizado.indexOf("venc") >= 0 ? "is-overdue" : "");
                            return '<div class="interconsulta-installments-table__row '+clase+'" role="row">'
                                +'<strong>#'+escaparHtmlSeguimientoHilo(numero)+'</strong>'
                                +'<span>'+escaparHtmlSeguimientoHilo(formatearFechaSeguimientoHilo(cuota.fecha_vencimiento || ""))+'</span>'
                                +'<span>'+escaparHtmlSeguimientoHilo(formatoMontoCuotaInterConsulta(pagado))+'</span>'
                                +'<span>'+escaparHtmlSeguimientoHilo(formatoMontoCuotaInterConsulta(saldo))+'</span>'
                                +'<span><b>'+escaparHtmlSeguimientoHilo(estado)+'</b>'+(Number(cuota.dias_mora || 0) > 0 ? '<small>'+Number(cuota.dias_mora)+' días</small>' : '')+'</span>'
                            +'</div>';
                        }).join("")
                    +'</div>';
                }
                panel.setAttribute("data-cargado", "1");
            } catch (error) {
                panel.innerHTML= '<div class="interconsulta-sale-selector__empty">'+escaparHtmlSeguimientoHilo(error.message || "No se pudieron consultar las cuotas.")+'</div>';
            }
        },
        error: function() {
            panel.innerHTML= '<div class="interconsulta-sale-selector__empty">No se pudieron consultar las cuotas. Intente nuevamente.</div>';
        },
        complete: function() {
            panel.removeAttribute("data-cargando");
        }
    });
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

function actualizarAvatarStackDetalleHilo(participantesServidor) {
    const contenedor= document.getElementById("avatarStackDetalle");
    const listado= document.getElementById("listadoMencionados");
    if (!contenedor || !listado) {
        return;
    }

    contenedor.innerHTML= "";
    let participantes= Array.isArray(participantesServidor) ? participantesServidor.map(function(item) {
        return {
            nombre: textoDetalleHilo(item.nombre_persona, "Participante"),
            url: textoDetalleHilo(item.url_usuario, "")
        };
    }) : Array.from(listado.querySelectorAll(".interconsulta-participant-item"))
        .map(function(item) {
            const nombre= item.querySelector(".interconsulta-participant-info span:not(.interconsulta-participant-avatar)");
            return {
                nombre: nombre ? nombre.textContent.trim() : item.textContent.trim(),
                url: String(item.getAttribute("data-avatar-url") || "").trim()
            };
        })
        .filter(function(item) {
            return item.nombre != "";
        });
    const participantesUnicos= {};
    participantes= participantes.filter(function(item) {
        const clave= item.nombre.toLowerCase();
        if (participantesUnicos[clave]) { return false; }
        participantesUnicos[clave]= true;
        return true;
    });

    if (participantes.length == 0) {
        contenedor.style.display= "none";
        return;
    }

    contenedor.style.display= "";
    const maximoVisible= 5;
    participantes.slice(0, maximoVisible).forEach(function(participante) {
        const avatar= document.createElement("span");
        avatar.className= "interconsulta-avatar-stack__item";
        if (participante.url) {
            const imagen= document.createElement("img");
            imagen.src= participante.url;
            imagen.alt= "Foto de " + participante.nombre;
            imagen.onerror= function() {
                avatar.textContent= inicialesParticipanteHilo(participante.nombre);
            };
            avatar.appendChild(imagen);
        } else {
            avatar.textContent= inicialesParticipanteHilo(participante.nombre);
        }
        avatar.title= tooltipParticipanteHilo(participante.nombre);
        contenedor.appendChild(avatar);
    });

    if (participantes.length > maximoVisible) {
        const resto= document.createElement("span");
        resto.className= "interconsulta-avatar-stack__item interconsulta-avatar-stack__more";
        resto.textContent= "+" + (participantes.length - maximoVisible);
        resto.title= participantes.slice(maximoVisible).map(function(item) { return tooltipParticipanteHilo(item.nombre); }).join(", ");
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

    const credencialColaborador= document.getElementById("credencialColaboradorDetalleInterConsulta");
    if (credencialColaborador) {
        const esColaborador= Number(datosHilo.esHiloColaborador || 0) > 0;
        credencialColaborador.hidden= !esColaborador;
        if (esColaborador) {
            const nombreColaborador= textoDetalleHilo(datosHilo.nombre_funcionario, "Colaborador");
            const cargoColaborador= textoDetalleHilo(datosHilo.cargo_funcionario, "Sin cargo informado");
            const areaColaborador= textoDetalleHilo(datosHilo.area_funcionario, "");
            const fotoColaborador= textoDetalleHilo(datosHilo.url_funcionario, "/GoodVentaAsisCap/iconos/sinperfil.png");
            credencialColaborador.innerHTML= '<img src="'+escaparHtmlSeguimientoHilo(fotoColaborador)+'" alt="Foto de '+escaparHtmlSeguimientoHilo(nombreColaborador)+'" onerror="this.src=\'/GoodVentaAsisCap/iconos/sinperfil.png\';">'
                +'<div><span>Credencial de colaborador</span><strong>'+escaparHtmlSeguimientoHilo(nombreColaborador)+'</strong>'
                +'<small>'+escaparHtmlSeguimientoHilo(cargoColaborador)+(areaColaborador ? ' - '+escaparHtmlSeguimientoHilo(areaColaborador) : '')+'</small>'
                +'<em>Perfil laboral conectado con Asistencia</em></div>';
        } else {
            credencialColaborador.innerHTML= "";
        }
    }

    const estadoClase= estado.toLowerCase().replace(/\s+/g, "-");
    document.getElementById("txtEstadoInterConsulta").className= "interconsulta-data-badge interconsulta-data-badge--" + estadoClase;
    document.getElementById("txtTipoInterConsulta").className= "interconsulta-data-badge";
    document.getElementById("badgeEstadoDetalle").className= "interconsulta-detail-badge interconsulta-detail-badge--" + estadoClase;
    document.getElementById("badgeEstadoDetalle").textContent= estado;
    document.getElementById("badgeTipoDetalle").textContent= tipo;
    document.getElementById("badgeLocalDetalle").textContent= local;
    mostrarBadgeDetalleHilo("badgeVinculadoDetalle", vinculado, "Hilo vinculado");
    mostrarBadgeDetalleHilo("badgeDictamenDetalle", tieneDictamen, "Dictamen registrado");
    var btnUnificarSeguimiento= document.getElementById("btnUnificarSeguimientoPaciente");
    if (btnUnificarSeguimiento) {
        btnUnificarSeguimiento.style.display= puedeUnificarSeguimiento ? "" : "none";
        btnUnificarSeguimiento.disabled= !puedeUnificarSeguimiento;
    }
    var btnDevolucionPagare= document.getElementById("btnDevolucionPagareDesdeHilo");
    if (btnDevolucionPagare) {
        var esHiloClienteVenta= Number(datosHilo.esHiloColaborador || 0) <= 0
            && ((codVentaFK != "" && codVentaFK != "0") || textoDetalleHilo(datosHilo.cod_clienteFK, "") != "");
        var flujoPagareDisponible= typeof centroFacturasAbrirDevolucionPagareDesdeHilo == "function";
        btnDevolucionPagare.style.display= esHiloClienteVenta && flujoPagareDisponible ? "" : "none";
        btnDevolucionPagare.disabled= !(esHiloClienteVenta && flujoPagareDisponible);
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
    const avisoPendiente= document.getElementById("avisoMensajesPendientesInterConsulta");
    const resumen= document.getElementById("contenedorEncabezadoInterConsulta");
    const filaSeleccionada= document.querySelector("#table_frm_VistaInterConsulta .interconsulta-thread-row--selected");

    return (avisoPendiente && avisoPendiente.style.display != "none")
        || (resumen && resumen.classList.contains("is-pending"))
        || (filaSeleccionada && filaSeleccionada.classList.contains("interconsulta-thread-row--pending"));
}

function actualizarVistaInterConsultaLeida() {
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
        const cantidadNoLeida= filaSeleccionada.querySelector("#td_datos_14");
        const totalFilaAntes= cantidadNoLeida ? Math.max(0, Number(cantidadNoLeida.textContent) || 0) : 0;
        filaSeleccionada.classList.remove("interconsulta-thread-row--pending");
        filaSeleccionada.querySelectorAll(".interconsulta-pending-badge").forEach(function(badgeFila) {
            badgeFila.remove();
        });
        if (cantidadNoLeida) {
            cantidadNoLeida.textContent= "0";
        }
        const avisoGlobal= document.getElementById("avisoMensajesPendientes");
        if (avisoGlobal && totalFilaAntes > 0) {
            const totalGlobalAntes= Math.max(0, Number(avisoGlobal.getAttribute("data-total-no-leidos")) || 0);
            actualizarTotalNoLeidosInterConsulta(Math.max(0, totalGlobalAntes - totalFilaAntes));
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
    // El filtro de subtipo ya no forma parte de la interfaz. Se conserva el
    // parametro vacio para mantener el contrato del listado legacy.
    const tipo= valorCampoInterConsulta("inptBuscarInterConsulta4");
    const ocultar_inactivos= document.getElementById('inptSeleccFiltroEstadoInterConsulta').checked;
    const usuario_vinculado= document.getElementById('inptUsuariosInterConsulta').value;
    const cod_localFK= document.getElementById('inptBuscarInterConsulta7').value;
    const busqueda_global= valorCampoInterConsulta("inptBuscarInterConsultaGlobal");
    const fecha_desde= valorCampoInterConsulta("inptBuscarInterConsultaFechaDesde");
    const fecha_hasta= valorCampoInterConsulta("inptBuscarInterConsultaFechaHasta");
    const categoria_principal= obtenerCategoriaActivaInterConsulta();
    const filtro_menciones= valorCampoInterConsulta("inptFiltroMencionesInterConsulta");
    actualizarResumenControlesInterConsulta();
    
    buscarPacientesConInterConsultas2(cod_interC, asunto, nombre_responsable, nombre_cliente, estado, tipo, cod_localFK, userid, limiteMaximoListadoInterConsulta, ocultar_inactivos, usuario_vinculado, busqueda_global, fecha_desde, fecha_hasta, categoria_principal, filtro_menciones);
}

function actualizarActividadDiariaSeguimientoInterConsulta(actividad) {
    const contenedor= document.getElementById("panelActividadDiariaInterConsulta");
    const etiqueta= document.getElementById("etiquetaActividadDiariaInterConsulta");
    if (!contenedor) {
        return;
    }
    if (actividad && typeof actividad == "object" && Array.isArray(actividad.usuarios)) {
        if (etiqueta) {
            etiqueta.textContent= actividad.etiqueta || ((actividad.fecha_desde || actividad.fecha_hasta) ? "Gestiones del periodo" : "Gestiones hoy");
        }
        if (!actividad.usuarios.length) {
            contenedor.innerHTML= '<div class="interconsulta-daily-activity__empty">Sin gestiones en el periodo</div>';
            return;
        }
        contenedor.innerHTML= actividad.usuarios.map(function(usuario) {
            var nombre= escaparHtmlSeguimientoHilo(usuario.nombre_persona || ("Usuario " + (usuario.cod_usuario || "")));
            var total= Number(usuario.total_gestiones || usuario.hilos_unicos || 0);
            var mensajes= Number(usuario.mensajes_manuales || 0);
            var seguimientos= Number(usuario.seguimientos_programados || 0);
            var citas= Number(usuario.citas_creadas || 0);
            var imagen= String(usuario.url_usuario || "").trim();
            var iniciales= escaparHtmlSeguimientoHilo(inicialesParticipanteHilo(usuario.nombre_persona || ""));
            var avatar= imagen
                ? '<span class="interconsulta-daily-activity__avatar"><span class="interconsulta-daily-activity__fallback">'+iniciales+'</span><img src="'+escaparHtmlSeguimientoHilo(imagen)+'" alt="" onerror="this.style.display=\'none\'"></span>'
                : '<span class="interconsulta-daily-activity__avatar"><span class="interconsulta-daily-activity__fallback">'+iniciales+'</span></span>';
            return '<article class="interconsulta-daily-activity__user" title="Cada hilo se cuenta una sola vez en el periodo">'
                +avatar
                +'<span class="interconsulta-daily-activity__detail"><strong>'+nombre+'</strong>'
                +'<small>Mensajes '+mensajes+' &middot; Seguimientos '+seguimientos+' &middot; Citas '+citas+'</small></span>'
                +'<b class="interconsulta-daily-activity__total">'+total+'<small> hilos</small></b>'
                +'</article>';
        }).join("");
        return;
    }
    if (etiqueta) {
        etiqueta.textContent= "Gestiones hoy";
    }
    contenedor.innerHTML= actividad || '<div class="interconsulta-daily-activity__empty">Sin gestiones hoy</div>';
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
        panelSeguimiento.setAttribute("data-contexto-hilo", obtenerHiloContextoSeguimientoInterConsulta());
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
    var hiloSolicitado= obtenerHiloContextoSeguimientoInterConsulta();
    if (!hiloSolicitado) {
        return;
    }
    if (solicitudContextoSeguimientoInterConsultaActiva && solicitudContextoSeguimientoInterConsultaActiva.readyState !== 4) {
        solicitudContextoSeguimientoInterConsultaActiva.abort();
    }
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
    datos.append("cod_interConsulta", hiloSolicitado);
    var solicitud= $.ajax({
        data: datos,
        url: "../php_system/abmInterConsulta.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        success: function(responseText) {
            if (obtenerHiloContextoSeguimientoInterConsulta() !== hiloSolicitado) {
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
            if (textstatus != "abort" && obtenerHiloContextoSeguimientoInterConsulta() === hiloSolicitado && botonProgramar) {
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

function obtenerHiloContextoSeguimientoInterConsulta() {
    var panel= document.getElementById("panelProgramarSeguimientoInterConsulta");
    var hiloPanel= panel ? String(panel.getAttribute("data-cod-hilo") || "") : "";
    return hiloPanel || String(codInterConsultaContextoSeguimiento || cod_interConsulta || "");
}

function obtenerDialogoLecturasMensajeInterConsulta() {
    var dialogo= document.getElementById("dialogoLecturasMensajeInterConsulta");
    if (dialogo) { return dialogo; }
    dialogo= document.createElement("div");
    dialogo.id= "dialogoLecturasMensajeInterConsulta";
    dialogo.className= "interconsulta-read-dialog";
    dialogo.hidden= true;
    dialogo.innerHTML= '<div class="interconsulta-read-dialog__backdrop" onclick="cerrarLecturasMensajeInterConsulta()"></div>'
        +'<section class="interconsulta-read-dialog__panel" role="dialog" aria-modal="true" aria-labelledby="tituloLecturasMensajeInterConsulta">'
        +'<header><div><span>Confirmaciones de lectura</span><h3 id="tituloLecturasMensajeInterConsulta">Visto por</h3></div>'
        +'<button type="button" onclick="cerrarLecturasMensajeInterConsulta()" aria-label="Cerrar"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></header>'
        +'<div class="interconsulta-read-dialog__body" id="contenidoLecturasMensajeInterConsulta"></div></section>';
    document.body.appendChild(dialogo);
    return dialogo;
}

function cerrarLecturasMensajeInterConsulta() {
    var dialogo= document.getElementById("dialogoLecturasMensajeInterConsulta");
    if (dialogo) { dialogo.hidden= true; }
    document.body.classList.remove("interconsulta-read-dialog-open");
}

function mostrarLecturasMensajeInterConsulta(codMensaje, boton) {
    codMensaje= Number(codMensaje) || 0;
    if (!codMensaje || !cod_interConsulta) { return; }
    var hiloSolicitado= String(cod_interConsulta || "");
    var dialogo= obtenerDialogoLecturasMensajeInterConsulta();
    var contenido= document.getElementById("contenidoLecturasMensajeInterConsulta");
    var loaderLecturas= window.TelarLoader && window.TelarLoader.html
        ? window.TelarLoader.html("Consultando lecturas...", "compact")
        : '<span role="status">Consultando lecturas...</span>';
    contenido.innerHTML= '<div class="interconsulta-read-dialog__state">' + loaderLecturas + '</div>';
    dialogo.hidden= false;
    document.body.classList.add("interconsulta-read-dialog-open");
    obtener_datos_user();
    var datos= new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "buscarLecturasMensajeInterConsulta");
    datos.append("cod_interConsulta", hiloSolicitado);
    datos.append("cod_mensaje", codMensaje);
    $.ajax({
        data: datos,
        url: "../php_system/abmInterConsulta.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        success: function(responseText) {
            try {
                var respuesta= typeof responseText == "string" ? $.parseJSON(responseText) : responseText;
                if (respuesta["1"] != "exito") {
                    throw new Error(mensajeRespuestaSeguimientoInterConsulta(respuesta, "No se pudieron consultar las lecturas."));
                }
                var lecturas= respuesta["2"] && Array.isArray(respuesta["2"].lecturas) ? respuesta["2"].lecturas : [];
                if (!lecturas.length) {
                    contenido.innerHTML= '<div class="interconsulta-read-dialog__state"><i class="fa-regular fa-clock" aria-hidden="true"></i><strong>Todav&iacute;a nadie m&aacute;s visualiz&oacute; este mensaje.</strong><span>El mensaje est&aacute; guardado correctamente.</span></div>';
                    return;
                }
                contenido.innerHTML= '<ul class="interconsulta-read-list">'+lecturas.map(function(lectura) {
                    var nombre= textoDetalleHilo(lectura.nombre_persona, "Usuario");
                    var foto= textoDetalleHilo(lectura.url_usuario, "/GoodVentaAsisCap/iconos/sinperfil.png");
                    var fecha= textoDetalleHilo(lectura.fecha_lectura, "Sin fecha");
                    return '<li><img src="'+escaparHtmlSeguimientoHilo(foto)+'" alt="Foto de '+escaparHtmlSeguimientoHilo(nombre)+'" onerror="this.src=\'/GoodVentaAsisCap/iconos/sinperfil.png\';">'
                        +'<div><strong>'+escaparHtmlSeguimientoHilo(nombre)+'</strong><span>Visto el '+escaparHtmlSeguimientoHilo(fecha)+'</span></div>'
                        +'<i class="fa-solid fa-check-double" aria-hidden="true"></i></li>';
                }).join("")+'</ul>';
            } catch (error) {
                contenido.innerHTML= '<div class="interconsulta-read-dialog__state interconsulta-read-dialog__state--error"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i><strong>'+escaparHtmlSeguimientoHilo(error.message || "No se pudieron consultar las lecturas.")+'</strong></div>';
            }
        },
        error: function() {
            contenido.innerHTML= '<div class="interconsulta-read-dialog__state interconsulta-read-dialog__state--error"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i><strong>No se pudieron consultar las lecturas.</strong></div>';
        }
    });
}

function abrirPanelSeguimientoProgramadoInterConsulta(codHilo, elementoOrigen) {
    var hiloActual= String(codHilo || cod_interConsulta || "");
    if (!hiloActual) {
        ver_vetana_informativa("Abra un hilo", "Primero seleccione el hilo donde desea programar el seguimiento.", "advertencia");
        return;
    }
    var panel= document.getElementById("panelProgramarSeguimientoInterConsulta");
    var boton= document.getElementById("btnAbrirSeguimientoProgramadoInterConsulta");
    if (!panel) {
        return;
    }
    codInterConsultaContextoSeguimiento= hiloActual;
    var detalle= document.getElementById("divAbmDetallesInterConsulta");
    var desdeListado= elementoOrigen && elementoOrigen.closest && elementoOrigen.closest("#divListadoInterConsulta")
        && detalle && window.getComputedStyle(detalle).display == "none";
    document.body.classList.toggle("interconsulta-followup-from-list", !!desdeListado);
    if (panel.getAttribute("data-cod-hilo") !== hiloActual) {
        reiniciarFormularioSeguimientoInterConsulta();
        panel.setAttribute("data-cod-hilo", hiloActual);
        panel.removeAttribute("data-contexto-hilo");
    }
    var token= document.getElementById("tokenSolicitudSeguimientoInterConsulta");
    if (!token || !token.value) {
        reiniciarFormularioSeguimientoInterConsulta();
    }
    elementoFocoAnteriorSeguimientoInterConsulta= elementoOrigen || (document.activeElement && !panel.contains(document.activeElement) ? document.activeElement : boton);
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
        panel.removeAttribute("data-cod-hilo");
        panel.removeAttribute("data-contexto-hilo");
    }
    document.body.classList.remove("interconsulta-followup-dialog-open");
    document.body.classList.remove("interconsulta-followup-from-list");
    if (boton) { boton.setAttribute("aria-expanded", "false"); }
    mostrarVistaFormularioSeguimientoInterConsulta(false);
    var focoAnterior= elementoFocoAnteriorSeguimientoInterConsulta;
    elementoFocoAnteriorSeguimientoInterConsulta= null;
    codInterConsultaContextoSeguimiento= "";
    if (restaurarFoco && focoAnterior && document.documentElement.contains(focoAnterior)) {
        enfocarElementoSeguimientoInterConsulta(focoAnterior);
    }
}

function alternarPanelSeguimientoProgramadoInterConsulta(codHilo, elementoOrigen) {
    var panel= document.getElementById("panelProgramarSeguimientoInterConsulta");
    if (!panel || panel.hidden) {
        abrirPanelSeguimientoProgramadoInterConsulta(codHilo, elementoOrigen);
    } else {
        cerrarPanelSeguimientoProgramadoInterConsulta();
    }
}

function abrirSeguimientoProgramadoDesdeListado(elemento, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    var hilo= elemento ? String(elemento.getAttribute("data-cod-interconsulta") || "") : "";
    abrirPanelSeguimientoProgramadoInterConsulta(hilo, elemento);
    return false;
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
    var hiloSolicitado= obtenerHiloContextoSeguimientoInterConsulta();
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
                    reiniciarFormularioSeguimientoInterConsulta();
                    cerrarPanelSeguimientoProgramadoInterConsulta();
                    if (String(cod_interConsulta) === hiloSolicitado) {
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
            if (boton && panel && panel.getAttribute("data-cod-hilo") === hiloSolicitado) {
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

function cancelarCargaMensajeCitadoInterConsulta() {
    secuenciaCargaMensajeCitadoInterConsulta++;
    if (solicitudCargaMensajeCitadoInterConsultaActiva && solicitudCargaMensajeCitadoInterConsultaActiva.readyState !== 4) {
        solicitudCargaMensajeCitadoInterConsultaActiva.abort();
    }
    solicitudCargaMensajeCitadoInterConsultaActiva= null;
    var timeline= document.getElementById("table_abm_InterConsulta");
    if (timeline) { timeline.removeAttribute("aria-busy"); }
}

function cancelarSolicitudContextoMensajeInterConsulta() {
    secuenciaContextoMensajeInterConsulta++;
    if (solicitudContextoMensajeInterConsultaActiva && solicitudContextoMensajeInterConsultaActiva.readyState !== 4) {
        solicitudContextoMensajeInterConsultaActiva.abort();
    }
    solicitudContextoMensajeInterConsultaActiva= null;
    cancelarCargaMensajeCitadoInterConsulta();
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
        var vistaRespuesta= document.getElementById("vistaRespuestaCitadaInterConsulta");
        vistaRespuesta.hidden= false;
        vistaRespuesta.setAttribute("aria-live", "polite");
        var editor= document.getElementById("inptContenidoAbmMensaje");
        if (vistaRespuesta && typeof vistaRespuesta.scrollIntoView == "function") {
            vistaRespuesta.scrollIntoView({behavior: "smooth", block: "nearest"});
        }
        if (editor) { editor.focus(); }
    });
}

function cancelarRespuestaCitadaInterConsulta(enfocarEditor) {
    var codigo= document.getElementById("codMensajeRespuestaInterConsulta");
    var vista= document.getElementById("vistaRespuestaCitadaInterConsulta");
    if (codigo) { codigo.value= ""; }
    if (vista) { vista.hidden= true; }
    if (enfocarEditor !== false) {
        var editor= document.getElementById("inptContenidoAbmMensaje");
        if (editor) { editor.focus(); }
    }
}

function mensajeCitadoVisibleInterConsulta(mensaje) {
    return !!(mensaje && (!mensaje.getClientRects || mensaje.getClientRects().length > 0));
}

function resaltarMensajeCitadoInterConsulta(codMensaje) {
    var mensaje= document.getElementById("mensajeInterConsulta-" + codMensaje);
    if (!mensajeCitadoVisibleInterConsulta(mensaje)) { return false; }
    var movimientoReducido= window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    mensaje.classList.remove("interconsulta-message-row--highlight");
    void mensaje.offsetWidth;
    mensaje.classList.add("interconsulta-message-row--highlight");
    mensaje.scrollIntoView({behavior: movimientoReducido ? "auto" : "smooth", block: "center"});
    setTimeout(function() { mensaje.classList.remove("interconsulta-message-row--highlight"); }, 2200);
    return true;
}

function mostrarPanelDictamenMensajeCitadoInterConsulta(panel, boton, callback) {
    if (!panel) {
        callback(new Error("No se encontro la seccion del mensaje original."));
        return;
    }
    if (!panel.classList.contains("show")) {
        if (typeof bootstrap !== "undefined" && bootstrap.Collapse) {
            bootstrap.Collapse.getOrCreateInstance(panel, {toggle: false}).show();
        } else {
            panel.classList.add("show");
            panel.style.display= "block";
        }
    }
    if (boton) {
        boton.setAttribute("aria-expanded", "true");
        var etiqueta= boton.querySelector("[data-label]");
        if (etiqueta) {
            etiqueta.textContent= boton.getAttribute("data-text-close") || "Ocultar mensajes relacionados";
        }
    }
    setTimeout(function() { callback(null, panel); }, 180);
}

function asegurarContenedorMensajeCitadoInterConsulta(contexto, callback) {
    var codDictamen= parseInt(contexto && contexto.cod_dictamenFK ? contexto.cod_dictamenFK : "0", 10);
    if (!codDictamen) {
        var contenedorPrincipal= document.getElementById("contenedorMensajesInterConsulta");
        if (!contenedorPrincipal) {
            callback(new Error("No se encontro la conversacion del hilo."));
            return;
        }
        callback(null, contenedorPrincipal);
        return;
    }

    var idPanelResolucion= "contenedorResolucionInterConsulta" + codDictamen;
    var idPanelMensajes= "contenedorMensajesInterConsulta" + codDictamen;
    var panelMensajes= document.getElementById(idPanelMensajes);
    var panelResolucion= document.getElementById(idPanelResolucion);
    var tarjeta= panelMensajes ? panelMensajes.closest(".interc-dictamen-card") : null;
    var boton= tarjeta ? tarjeta.querySelector('[aria-controls="' + idPanelMensajes + '"]') : null;
    if (!panelMensajes || !panelResolucion || !tarjeta || !boton) {
        callback(new Error("No se encontro la resolucion que contiene el mensaje original."));
        return;
    }
    if (tarjeta.dataset.detalleCargado == "1") {
        mostrarPanelDictamenMensajeCitadoInterConsulta(panelMensajes, boton, callback);
        return;
    }
    cargarPanelDictamenInterConsulta(codDictamen, idPanelResolucion, idPanelMensajes, boton, function(error) {
        if (error) {
            callback(error);
            return;
        }
        mostrarPanelDictamenMensajeCitadoInterConsulta(panelMensajes, boton, callback);
    });
}

function htmlBotonMasMensajesInterconsulta(offset, codDictamen) {
    var siguienteOffset= Math.max(0, parseInt(offset || "0", 10));
    var codigoDictamen= String(codDictamen || "");
    return '<div data-role="dictamen-boton-mas" data-next-offset="'+siguienteOffset+'" data-cod-dictamen="'+escaparHtmlSeguimientoHilo(codigoDictamen)+'" style="width:100%;display:flex;justify-content:center;margin-bottom:12px;">'
        +'<button type="button" class="btn btn-success" onclick=\'verMasMensajesInterconsulta('+siguienteOffset+', '+JSON.stringify(codigoDictamen)+')\'>Ver m&aacute;s mensajes...</button>'
        +'</div>';
}

function panelListaMensajesInterConsulta(contenedor) {
    if (!contenedor) { return null; }
    return contenedor.querySelector('[data-role="dictamen-mensajes"]')
        || contenedor.querySelector('[data-role="dictamen-chat-panel"]')
        || contenedor;
}

function panelLecturaMensajesInterConsulta(contenedor) {
    if (!contenedor) { return null; }
    return contenedor.querySelector('[data-role="dictamen-chat-panel"]') || contenedor;
}

function offsetSiguienteMensajesInterConsulta(contenedor) {
    var botonMas= contenedor ? contenedor.querySelector('[data-role="dictamen-boton-mas"]') : null;
    if (!botonMas) { return 0; }
    var offset= parseInt(botonMas.getAttribute("data-next-offset") || "", 10);
    if (!isNaN(offset)) { return offset; }
    var boton= botonMas.querySelector("button[onclick]");
    var coincidencia= boton ? String(boton.getAttribute("onclick") || "").match(/verMasMensajesInterconsulta\((\d+)/) : null;
    return coincidencia ? parseInt(coincidencia[1], 10) : 0;
}

function insertarTramoMensajeCitadoInterConsulta(contenedor, html, siguienteOffset, totalMensajes, codDictamen) {
    var lista= panelListaMensajesInterConsulta(contenedor);
    var panelLectura= panelLecturaMensajesInterConsulta(contenedor);
    if (!lista || !panelLectura) { return false; }
    contenedor.dataset.totalMensajes= String(Math.max(0, parseInt(totalMensajes || "0", 10)));
    var botonActual= contenedor.querySelector('[data-role="dictamen-boton-mas"]');
    if (botonActual) { botonActual.remove(); }
    if (html) { lista.insertAdjacentHTML("afterbegin", html); }
    if (siguienteOffset < totalMensajes) {
        panelLectura.insertAdjacentHTML("afterbegin", htmlBotonMasMensajesInterconsulta(siguienteOffset, codDictamen));
    }
    return true;
}

function cargarTramoMensajeCitadoInterConsulta(codMensaje, contexto, contenedor, offsetDesde, secuenciaSolicitud, callback) {
    var hiloSolicitado= String(cod_interConsulta || "");
    obtener_datos_user();
    var datos= new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "cargarMensajeCitadoInterConsulta");
    datos.append("cod_interConsulta", hiloSolicitado);
    datos.append("cod_mensaje", codMensaje);
    datos.append("offset_desde", Math.max(0, parseInt(offsetDesde || "0", 10)));
    var solicitud= $.ajax({
        data: datos,
        url: "../php_system/abmInterConsulta.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        success: function(responseText) {
            if (secuenciaSolicitud !== secuenciaCargaMensajeCitadoInterConsulta || String(cod_interConsulta) !== hiloSolicitado) { return; }
            try {
                var respuesta= $.parseJSON(responseText);
                if (respuesta["1"] != "exito") {
                    throw new Error(mensajeRespuestaSeguimientoInterConsulta(respuesta, "No se pudo cargar el mensaje original."));
                }
                var resultado= respuesta["2"] || {};
                var siguienteOffset= parseInt(resultado.offset_siguiente || "0", 10);
                var totalMensajes= parseInt(resultado.total_mensajes || "0", 10);
                var offsetObjetivo= parseInt(resultado.offset_objetivo || "0", 10);
                var codDictamen= resultado.cod_dictamenFK || (contexto ? contexto.cod_dictamenFK : "") || "";
                if (!insertarTramoMensajeCitadoInterConsulta(contenedor, resultado.html || "", siguienteOffset, totalMensajes, codDictamen)) {
                    throw new Error("No se encontro el panel donde cargar el mensaje original.");
                }
                if (resaltarMensajeCitadoInterConsulta(codMensaje)) {
                    callback(null);
                    return;
                }
                if (siguienteOffset > parseInt(offsetDesde || "0", 10) && siguienteOffset <= offsetObjetivo) {
                    cargarTramoMensajeCitadoInterConsulta(codMensaje, contexto, contenedor, siguienteOffset, secuenciaSolicitud, callback);
                    return;
                }
                throw new Error("El mensaje original no pudo posicionarse dentro del historial.");
            } catch (error) {
                callback(error);
            }
        },
        error: function(jqXHR, textstatus) {
            if (textstatus != "abort" && secuenciaSolicitud === secuenciaCargaMensajeCitadoInterConsulta && String(cod_interConsulta) === hiloSolicitado) {
                callback(new Error("No se pudo cargar el historial del mensaje original."));
            }
        },
        complete: function() {
            if (solicitudCargaMensajeCitadoInterConsultaActiva === solicitud) {
                solicitudCargaMensajeCitadoInterConsultaActiva= null;
            }
        }
    });
    solicitudCargaMensajeCitadoInterConsultaActiva= solicitud;
}

function mostrarContextoMensajeCitadoInterConsulta(contexto, error) {
    var texto= escaparHtmlSeguimientoHilo(String(contexto && contexto.contenido ? contexto.contenido : "").replace(/@\{\d+\}/g, "@usuario")).replace(/\n/g, "<br>");
    var autor= escaparHtmlSeguimientoHilo(contexto && contexto.nombre_persona ? contexto.nombre_persona : "un participante");
    var detalle= error && error.message ? escaparHtmlSeguimientoHilo(error.message) + "<br><br>" : "";
    ver_vetana_informativa("Mensaje original de " + autor, detalle + (texto || "Mensaje sin texto"), "advertencia");
}

function irMensajeCitadoInterConsulta(codMensaje) {
    if (!codMensaje) { return; }
    if (resaltarMensajeCitadoInterConsulta(codMensaje)) { return; }
    solicitarContextoMensajeInterConsulta(codMensaje, function(error, contexto) {
        if (error) {
            ver_vetana_informativa("Mensaje no disponible", error.message, "advertencia");
            return;
        }
        var secuenciaContextoActual= secuenciaContextoMensajeInterConsulta;
        asegurarContenedorMensajeCitadoInterConsulta(contexto, function(errorContenedor, contenedor) {
            if (secuenciaContextoActual !== secuenciaContextoMensajeInterConsulta) { return; }
            if (errorContenedor) {
                mostrarContextoMensajeCitadoInterConsulta(contexto, errorContenedor);
                return;
            }
            if (resaltarMensajeCitadoInterConsulta(codMensaje)) { return; }
            cancelarCargaMensajeCitadoInterConsulta();
            var secuenciaSolicitud= ++secuenciaCargaMensajeCitadoInterConsulta;
            var offsetDesde= offsetSiguienteMensajesInterConsulta(contenedor);
            var timeline= document.getElementById("table_abm_InterConsulta");
            if (timeline) { timeline.setAttribute("aria-busy", "true"); }
            cargarTramoMensajeCitadoInterConsulta(codMensaje, contexto, contenedor, offsetDesde, secuenciaSolicitud, function(errorCarga) {
                if (timeline) { timeline.removeAttribute("aria-busy"); }
                if (errorCarga && secuenciaSolicitud === secuenciaCargaMensajeCitadoInterConsulta) {
                    mostrarContextoMensajeCitadoInterConsulta(contexto, errorCarga);
                }
            });
        });
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
    notificarSeguimientosVencidosInterConsulta(lista);
}

function fechaSeguimientoInterConsulta(valor) {
    var texto= String(valor || "").trim();
    var partes= texto.match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2})(?::(\d{2}))?)?$/);
    if (partes) {
        return new Date(Number(partes[1]), Number(partes[2]) - 1, Number(partes[3]), Number(partes[4] || 0), Number(partes[5] || 0), Number(partes[6] || 0));
    }
    return new Date(texto.replace(" ", "T"));
}

function textoTiempoSeguimientoInterConsulta(fechaObjetivo) {
    var diferencia= fechaObjetivo.getTime() - Date.now();
    var vencida= diferencia < 0;
    var minutos= Math.max(0, Math.floor(Math.abs(diferencia) / 60000));
    var dias= Math.floor(minutos / 1440);
    var horas= Math.floor((minutos % 1440) / 60);
    var minutosRestantes= minutos % 60;
    var hoy= new Date();
    var mismoDia= fechaObjetivo.getFullYear() == hoy.getFullYear()
        && fechaObjetivo.getMonth() == hoy.getMonth()
        && fechaObjetivo.getDate() == hoy.getDate();
    if (!vencida && mismoDia) {
        return "Vence hoy a las " + dosDigitosInterConsulta(fechaObjetivo.getHours()) + ":" + dosDigitosInterConsulta(fechaObjetivo.getMinutes());
    }
    var partes= [];
    if (dias > 0) { partes.push(dias + (dias == 1 ? " día" : " días")); }
    if (horas > 0 && partes.length < 2) { partes.push(horas + (horas == 1 ? " hora" : " horas")); }
    if (dias == 0 && minutosRestantes > 0 && partes.length < 2) { partes.push(minutosRestantes + " min"); }
    if (!partes.length) { partes.push("menos de 1 min"); }
    return (vencida ? "Vencida hace " : "Vence en ") + partes.join(" y ");
}

function actualizarCuentaRegresivaSeguimientosInterConsulta() {
    document.querySelectorAll("#divListadoInterConsulta [data-fecha-programada], #divAbmDetallesInterConsulta [data-fecha-programada]").forEach(function(elemento) {
        var fecha= fechaSeguimientoInterConsulta(elemento.getAttribute("data-fecha-programada"));
        if (isNaN(fecha.getTime())) { return; }
        var selectorDestino= elemento.classList.contains("interconsulta-followup-card")
            ? ".interconsulta-followup-card__details"
            : ".interconsulta-management-summary__body";
        var destino= elemento.querySelector(selectorDestino) || elemento;
        var contador= elemento.querySelector(".interconsulta-followup-remaining");
        if (!contador) {
            contador= document.createElement("small");
            contador.className= "interconsulta-followup-remaining";
            destino.appendChild(contador);
        }
        var vencida= fecha.getTime() < Date.now();
        contador.textContent= textoTiempoSeguimientoInterConsulta(fecha);
        contador.classList.toggle("is-overdue", vencida);
        elemento.classList.toggle("is-countdown-overdue", vencida);
    });
}

function notificarSeguimientosVencidosInterConsulta(items) {
    var pendientes= [];
    (items || []).forEach(function(item) {
        var fecha= fechaSeguimientoInterConsulta(item.fecha_programada);
        if (isNaN(fecha.getTime()) || fecha.getTime() >= Date.now()) { return; }
        var clave= String(userid || "0") + "-" + String(item.id_seguimiento || "0") + "-vencido";
        var almacenada= false;
        try { almacenada= window.sessionStorage && sessionStorage.getItem("alertaSeguimiento-" + clave) == "1"; } catch (error) {}
        if (almacenada || avisosSeguimientoMostradosInterConsulta[clave]) { return; }
        pendientes.push({item: item, clave: clave});
    });
    if (!pendientes.length || typeof ver_vetana_informativa != "function") { return; }
    var resumen= pendientes.slice(0, 3).map(function(registro) {
        var codigo= parseInt(registro.item.cod_interConsultaFK || "0", 10) || "";
        var motivo= escaparHtmlSeguimientoHilo(String(registro.item.motivo || "seguimiento pendiente"));
        return "Hilo #" + String(codigo) + ": " + motivo;
    });
    if (pendientes.length > 3) {
        resumen.push("Y " + String(pendientes.length - 3) + " seguimiento(s) vencido(s) mas");
    }
    ver_vetana_informativa(
        pendientes.length == 1 ? "Seguimiento vencido" : "Seguimientos vencidos",
        resumen.join("<br>") + ".<br>Puede abrirlos desde el panel de alertas.",
        "advertencia"
    );
    pendientes.forEach(function(registro) {
        avisosSeguimientoMostradosInterConsulta[registro.clave]= true;
        try { if (window.sessionStorage) { sessionStorage.setItem("alertaSeguimiento-" + registro.clave, "1"); } } catch (error) {}
    });
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

function moduloHilosInterConsultaVisible() {
    if (document.visibilityState && document.visibilityState !== "visible") { return false; }
    var ids= ["divListadoInterConsulta", "divAbmDetallesInterConsulta", "divAbmInterConsulta"];
    for (var i= 0; i < ids.length; i++) {
        var elemento= document.getElementById(ids[i]);
        if (elemento && window.getComputedStyle(elemento).display !== "none") { return true; }
    }
    return false;
}

function actualizarResumenHilosInterConsulta() {
    if (!moduloHilosInterConsultaVisible()) { return; }
    obtener_datos_user();
    if (!userid) { return; }
    if (solicitudResumenHilosInterConsultaActiva && solicitudResumenHilosInterConsultaActiva.readyState !== 4) {
        return;
    }
    var datos= new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "buscarResumenHilos");
    var solicitud= $.ajax({
        data: datos,
        url: "../php_system/abmInterConsulta.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        success: function(responseText) {
            try {
                var respuesta= $.parseJSON(responseText);
                if (respuesta["1"] !== "exito") { return; }
                var resumen= respuesta["2"] || {};
                actualizarTabsCategoriaHilosInterConsulta(resumen.conteos || {});
                actualizarTotalNoLeidosInterConsulta(resumen.no_leidos || 0);
                actualizarAlertasSeguimientoInterConsulta(resumen.alertas || {});
            } catch (error) {}
        },
        complete: function() {
            if (solicitudResumenHilosInterConsultaActiva === solicitud) {
                solicitudResumenHilosInterConsultaActiva= null;
            }
        }
    });
    solicitudResumenHilosInterConsultaActiva= solicitud;
}

function inicializarResumenPeriodicoHilosInterConsulta() {
    if (!temporizadorResumenHilosInterConsulta) {
        temporizadorResumenHilosInterConsulta= setInterval(actualizarResumenHilosInterConsulta, intervaloResumenHilosInterConsultaMs);
    }
    if (!manejadorVisibilidadResumenHilosInterConsultaInicializado) {
        document.addEventListener("visibilitychange", function() {
            if (!document.hidden && moduloHilosInterConsultaVisible()) {
                actualizarResumenHilosInterConsulta();
            }
        });
        manejadorVisibilidadResumenHilosInterConsultaInicializado= true;
    }
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
    inicializarResumenPeriodicoHilosInterConsulta();
    actualizarCuentaRegresivaSeguimientosInterConsulta();
    if (!temporizadorCuentaRegresivaSeguimientoInterConsulta) {
        temporizadorCuentaRegresivaSeguimientoInterConsulta= setInterval(actualizarCuentaRegresivaSeguimientosInterConsulta, 60000);
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
    datos.append("filtro_menciones", filtros.filtro_menciones || "");
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

function actualizarTotalNoLeidosInterConsulta(total) {
    total= Math.max(0, Number(total) || 0);
    var aviso= document.getElementById("avisoMensajesPendientes");
    if (!aviso) { return; }
    aviso.style.display= total > 0 ? "" : "none";
    aviso.textContent= total == 1 ? "1 mensaje sin leer" : total + " mensajes sin leer";
    aviso.setAttribute("data-total-no-leidos", String(total));
    aviso.setAttribute("aria-label", aviso.textContent);
}

function aplicarPaginaListadoInterConsulta(datos, offsetPagina, esEnriquecido) {
    var listado= document.getElementById('table_frm_VistaInterConsulta');
    var contenedorScroll= document.querySelector("#divListadoInterConsulta .hilos-thread-table-wrap");
    var scrollAnterior= contenedorScroll ? contenedorScroll.scrollTop : 0;
    if (listado) {
        listado.innerHTML= datos["2"];
    }
    actualizarCuentaRegresivaSeguimientosInterConsulta();
    inicializarArrastreHilosInterConsulta();
    inicializarAccionesGestionProgramadaListadoInterConsulta();
    if (esEnriquecido && contenedorScroll) {
        contenedorScroll.scrollTop= scrollAnterior;
    } else {
        desplazarListadoHilosAlInicio();
    }

    registrocargadoInterConsulta= Number(datos["4"]) || 0;
    paginaOffsetInterConsulta= Math.max(0, Number(offsetPagina) || 0);
    registroInterConsultaAbierta= Number(datos["7"]) || 0;
    actualizarTotalNoLeidosInterConsulta(datos["6"]);
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
        actualizarActividadDiariaSeguimientoInterConsulta(datos["13"] || datos["10"]);
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

function buscarPacientesConInterConsultas2(cod_interC, asunto, nombre_responsable, nombre_cliente, estado, tipo, cod_localFK, cod_usuarioFK, limite, ocultar_inactivos, usuario_vinculado, busqueda_global= "", fecha_desde= "", fecha_hasta= "", categoria_principal= "", filtro_menciones= "") {
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
        filtro_menciones: filtro_menciones,
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
        guardarFiltrosBusquedaInterConsulta(cod_interC, asunto, nombre_responsable, nombre_cliente, estado, tipo, cod_localFK, cod_usuarioFK, ocultar_inactivos, usuario_vinculado, busqueda_global, fecha_desde, fecha_hasta, categoria_principal, filtro_menciones);
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
                    actualizarTotalNoLeidosInterConsulta(datos["6"]);

                    // Una busqueda unica
                    if (limite == 0) {
                        if (!cod_usuarioFK) {
                            document.getElementById('listAsuntoAbmInterConsulta').innerHTML= datos[8];
                        } else {
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
        filtros.filtro_menciones,
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

function buscarMasPacientesConInterConsultas2(cod_interC, cod_usuarioFK, asunto, nombre_responsable, nombre_cliente, estado, tipo, cod_localFK, limite, ocultar_inactivos, usuario_vinculado, busqueda_global= "", fecha_desde= "", fecha_hasta= "", categoria_principal= "", filtro_menciones= "", offsetPagina= 0) {
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
        filtro_menciones: filtro_menciones,
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
    var hiloOrigen= parseInt(cod_interConsulta, 10) || 0;
    var hiloDestino= parseInt(id_interconsulta_destino, 10) || 0;
    if (!hiloOrigen || !hiloDestino || hiloOrigen === hiloDestino) {
        ver_vetana_informativa("No se puede fusionar", "Seleccione dos hilos diferentes.", "advertencia");
        return;
    }

    let datos= new FormData();
    datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", 'previsualizarFusionInterConsultas');
    datos.append("cod_interConsulta_destino", hiloDestino);
    datos.append("cod_interConsulta", hiloOrigen);
    
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
			try {
				var vista = $.parseJSON(responseText);
				if (!vista || !vista.ok) {
                    ver_vetana_informativa("No se puede fusionar", (vista && vista.mensaje) || "No fue posible validar ambos hilos.", "advertencia");
                    return;
				}
                var resumen= vista.resumen || {};
                var detalle= [
                    "Hilo que se archivara: #" + vista.origen.cod_interConsulta + " - " + (vista.origen.asunto || "Sin asunto"),
                    "Hilo maestro: #" + vista.destino.cod_interConsulta + " - " + (vista.destino.asunto || "Sin asunto"),
                    "",
                    "Registros que se incorporaran al hilo maestro:",
                    "- Mensajes: " + (parseInt(resumen.mensajes, 10) || 0),
                    "- Dictamenes: " + (parseInt(resumen.dictamenes, 10) || 0),
                    "- Gestiones programadas: " + (parseInt(resumen.seguimientos, 10) || 0),
                    "- Ventas vinculadas: " + (parseInt(resumen.ventas_vinculadas, 10) || 0),
                    "- Documentos de pagare: " + (parseInt(resumen.documentos_pagare, 10) || 0),
                    "- Recetas e indicaciones: " + (parseInt(resumen.recetas_indicaciones, 10) || 0),
                    "",
                    "No se eliminara ningun registro. El hilo origen quedara archivado y el timeline se ordenara por la fecha y hora en que cada elemento fue guardado.",
                    "",
                    "¿Desea continuar?"
                ];
                if (vista.advertencia_colaborador) {
                    detalle.splice(detalle.length - 3, 0, "ADVERTENCIA: " + vista.advertencia_colaborador, "");
                }
                if (confirm(detalle.join("\n"))) {
                    ejecutarFusionInterConsultasConfirmada(hiloOrigen, hiloDestino);
                }
			} catch (error) {
				ver_vetana_informativa("No se pudo preparar la fusion", "La respuesta del servidor no es valida.", "advertencia");
                var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			} finally {
                verCerrarEfectoCargando("");
            }
		}
	});
}

function ejecutarFusionInterConsultasConfirmada(hiloOrigen, hiloDestino) {
    let datos= new FormData();
    datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", 'fusionarInterConsultas');
    datos.append("cod_interConsulta_destino", hiloDestino);
    datos.append("cod_interConsulta", hiloOrigen);

    verCerrarEfectoCargando("1");
    $.ajax({
        data: datos,
        url: "../php_system/abmInterConsulta.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        error: function (jqXHR, textstatus) {
            manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
            ver_vetana_informativa("No se pudo fusionar", "Revise la conexion y vuelva a intentar.", "advertencia");
        },
        success: function (responseText) {
            try {
                var respuesta= $.parseJSON(responseText);
                if (respuesta["1"] !== "exito") {
                    ver_vetana_informativa("No se pudo fusionar", respuesta["2"] || "La operacion fue rechazada.", "advertencia");
                    return;
                }
                ver_vetana_informativa(
                    "Fusion completada",
                    "El hilo #" + hiloOrigen + " quedo archivado. Toda su informacion fue incorporada al hilo maestro #" + hiloDestino + " y la operacion quedo registrada en el timeline.",
                    "info"
                );
                verCerrarVentanaInterConsulta(false, 'divListadoInterConsulta');
                verCerrarVentanaDetalleInterConsulta(false);
            } catch (error) {
                ver_vetana_informativa("No se pudo confirmar la fusion", "La respuesta del servidor no es valida.", "advertencia");
                GuardarArchivosLog("Error: "+error+" \r\n Consola: "+responseText);
            }
        },
        complete: function () {
            verCerrarEfectoCargando("");
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
    const cambioClasificacion= !!cod_interConsulta && (
        categoriaOriginalAbmInterConsulta != categoriaSeleccionada
        || normalizarTextoHiloInterConsulta(tipoOriginalAbmInterConsulta) != normalizarTextoHiloInterConsulta(tipo)
    );

    if (String(cod_ventaFKConsulta || "").trim() != "" && String(cod_ventaFKConsulta || "").trim() != "0") {
        if (cambioClasificacion) {
            var categoriaAnteriorAutomatica= categoriasHilosInterConsulta[categoriaOriginalAbmInterConsulta] ? categoriasHilosInterConsulta[categoriaOriginalAbmInterConsulta].nombre : categoriaOriginalAbmInterConsulta;
            var categoriaNuevaAutomatica= categoriasHilosInterConsulta[categoriaSeleccionada] ? categoriasHilosInterConsulta[categoriaSeleccionada].nombre : categoriaSeleccionada;
            if (!confirm("Se cambiará únicamente la clasificación del hilo de " + categoriaAnteriorAutomatica + " a " + categoriaNuevaAutomatica + ". La venta, el paciente y todo el historial se conservarán. ¿Desea continuar?")) {
                return false;
            }
            reclasificarHiloInterConsulta(cod_interConsulta, categoriaSeleccionada, tipo, {
                alCompletar: function() {
                    categoriaOriginalAbmInterConsulta= categoriaSeleccionada;
                    tipoOriginalAbmInterConsulta= tipo;
                    verCerrarVentanaInterConsulta(false);
                }
            });
            return false;
        }
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
var tipoAdjuntoDocumentoGasto= "otro";
var datosAdjuntoDocumentoGasto= null;
var nombreArchivoAdjuntoDocumentoGasto= "";
var contextoAdjuntoDocumentoGuiado= null;
var solicitudContextoAdjuntoDocumentoGuiado= null;
var adjuntoDocumentoGuiadoEstado= {
    origen: "",
    tipo: "",
    archivo: "",
    extension: "",
    nombreArchivo: "",
    mime: "",
    lecturaArchivo: 0,
    guardando: false,
    focoAnterior: null,
    hilo: "",
    tipoMovimiento: "Egreso"
};

function escaparHtmlAdjuntoDocumentoGuiado(valor) {
    return String(valor === null || valor === undefined ? "" : valor)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function normalizarMontoAdjuntoDocumentoGuiado(valor) {
    var texto= String(valor || "").replace(/Gs\.?/gi, "").replace(/\s/g, "");
    if (texto.indexOf(",") >= 0) {
        texto= texto.replace(/\./g, "").replace(",", ".");
    } else if ((texto.match(/\./g) || []).length > 1 || /^\d{1,3}(\.\d{3})+$/.test(texto)) {
        texto= texto.replace(/\./g, "");
    }
    texto= texto.replace(/[^0-9.-]/g, "");
    var numero= Number(texto);
    return Number.isFinite(numero) ? numero : 0;
}

function formatearMontoAdjuntoDocumentoGuiado(valor) {
    var numero= normalizarMontoAdjuntoDocumentoGuiado(valor);
    try {
        return new Intl.NumberFormat("es-PY", {maximumFractionDigits: 2}).format(numero);
    } catch (error) {
        return String(numero);
    }
}

function obtenerFechaHoyAdjuntoDocumentoGuiado() {
    var fecha= new Date();
    var offset= fecha.getTimezoneOffset();
    return new Date(fecha.getTime() - (offset * 60000)).toISOString().slice(0, 10);
}

function mostrarErrorAdjuntoDocumentoGuiado(mensaje) {
    var error= document.getElementById("errorAdjuntoDocumentoGuiado");
    if (!error) { return; }
    error.textContent= mensaje || "";
    error.hidden= !mensaje;
}

function obtenerProveedorAdjuntoDocumentoGuiado() {
    var select= document.getElementById("proveedorAdjuntoDocumento");
    if (!select || !select.value) { return null; }
    var opcion= select.options[select.selectedIndex];
    return {
        cod_proveedor: Number(select.value || 0),
        nombre_persona: opcion ? opcion.getAttribute("data-nombre") || opcion.textContent : "",
        rut_proveedor: opcion ? opcion.getAttribute("data-ruc") || "" : ""
    };
}

function cargarProveedoresAdjuntoDocumentoGuiado(proveedores) {
    var select= document.getElementById("proveedorAdjuntoDocumento");
    if (!select) { return; }
    var valorActual= select.value;
    select.innerHTML= "";
    var manual= document.createElement("option");
    manual.value= "";
    manual.textContent= "No está registrado / cargar manualmente";
    select.appendChild(manual);
    (proveedores || []).forEach(function(proveedor) {
        var opcion= document.createElement("option");
        opcion.value= proveedor.cod_proveedor || "";
        opcion.textContent= proveedor.nombre_persona || "Proveedor sin nombre";
        opcion.setAttribute("data-nombre", proveedor.nombre_persona || "");
        opcion.setAttribute("data-ruc", proveedor.rut_proveedor || "");
        select.appendChild(opcion);
    });
    if (valorActual && select.querySelector('option[value="' + String(valorActual).replace(/"/g, "") + '"]')) {
        select.value= valorActual;
    }
}

function cargarContextoAdjuntoDocumentoGuiado() {
    if (contextoAdjuntoDocumentoGuiado) {
        configurarTiposAdjuntoDocumentoGuiado();
        cargarProveedoresAdjuntoDocumentoGuiado(contextoAdjuntoDocumentoGuiado.proveedores || []);
        return;
    }
    if (solicitudContextoAdjuntoDocumentoGuiado) { return; }
    obtener_datos_user();
    var datos= new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "contextoAdjuntoDocumento");
    datos.append("cod_interConsulta", adjuntoDocumentoGuiadoEstado.origen == "hilo" ? adjuntoDocumentoGuiadoEstado.hilo : "");
    solicitudContextoAdjuntoDocumentoGuiado= $.ajax({
        data: datos,
        url: "../php_system/abmInterConsulta.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        complete: function() {
            solicitudContextoAdjuntoDocumentoGuiado= null;
        },
        error: function(jqXHR, textstatus) {
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
            configurarTiposAdjuntoDocumentoGuiado();
        },
        success: function(responseText) {
            try {
                var respuesta= $.parseJSON(responseText);
                if (respuesta["1"] != "exito") {
                    throw new Error(respuesta.mensaje || "No se pudo cargar el contexto documental.");
                }
                contextoAdjuntoDocumentoGuiado= respuesta["2"] || {};
                cargarProveedoresAdjuntoDocumentoGuiado(contextoAdjuntoDocumentoGuiado.proveedores || []);
                configurarTiposAdjuntoDocumentoGuiado();
            } catch (error) {
                mostrarErrorAdjuntoDocumentoGuiado(error.message || "No se pudo cargar la lista de proveedores.");
            }
        }
    });
}

function configurarTiposAdjuntoDocumentoGuiado() {
    var contexto= contextoAdjuntoDocumentoGuiado || {};
    var permisos= contexto.permisos || {};
    var origen= adjuntoDocumentoGuiadoEstado.origen;
    var esEgreso= adjuntoDocumentoGuiadoEstado.tipoMovimiento == "Egreso";
    var permiteFinanciero= origen == "hilo"
        ? Number(permisos.REGISTRARFACTURAHILO || 0) == 1
        : (esEgreso && Number(permisos.REGISTRARFACTURAMANUAL || 0) == 1);
    document.querySelectorAll("#tiposAdjuntoDocumentoGuiado [data-tipo-adjunto]").forEach(function(boton) {
        var tipo= boton.getAttribute("data-tipo-adjunto");
        boton.hidden= tipo != "otro" && !permiteFinanciero;
    });
    var tituloOtro= document.getElementById("tituloTipoOtroAdjuntoDocumento");
    var ayudaOtro= document.getElementById("ayudaTipoOtroAdjuntoDocumento");
    if (origen == "gasto" && adjuntoDocumentoGuiadoEstado.tipoMovimiento == "Deposito") {
        tituloOtro.textContent= "Comprobante de depósito";
        ayudaOtro.textContent= "Respaldo general, sin ingreso al Centro de Facturas y Documentos";
    } else if (origen == "gasto" && adjuntoDocumentoGuiadoEstado.tipoMovimiento == "Ingreso") {
        tituloOtro.textContent= "Comprobante o imagen";
        ayudaOtro.textContent= "Respaldo del ingreso, sin factura recibida";
    } else {
        tituloOtro.textContent= "Imagen o archivo";
        ayudaOtro.textContent= "Respaldo simple, sin datos fiscales";
    }
    if (adjuntoDocumentoGuiadoEstado.tipo && document.querySelector('#tiposAdjuntoDocumentoGuiado [data-tipo-adjunto="' + adjuntoDocumentoGuiadoEstado.tipo + '"][hidden]')) {
        seleccionarTipoAdjuntoDocumentoGuiado("otro");
    }
}

function limpiarFormularioAdjuntoDocumentoGuiado() {
    adjuntoDocumentoGuiadoEstado.lecturaArchivo++;
    ["razonSocialAdjuntoDocumento", "rucAdjuntoDocumento", "numeroAdjuntoDocumento", "montoAdjuntoDocumento", "descripcionAdjuntoDocumento"].forEach(function(id) {
        var campo= document.getElementById(id);
        if (campo) { campo.value= ""; campo.readOnly= false; }
    });
    var proveedor= document.getElementById("proveedorAdjuntoDocumento");
    if (proveedor) { proveedor.value= ""; }
    var fecha= document.getElementById("fechaAdjuntoDocumento");
    if (fecha) { fecha.value= obtenerFechaHoyAdjuntoDocumentoGuiado(); }
    var archivo= document.getElementById("archivoAdjuntoDocumento");
    if (archivo) { archivo.value= ""; }
    var nombre= document.getElementById("nombreArchivoAdjuntoDocumento");
    if (nombre) { nombre.textContent= "Todavía no se seleccionó un archivo."; }
    adjuntoDocumentoGuiadoEstado.tipo= "";
    adjuntoDocumentoGuiadoEstado.archivo= "";
    adjuntoDocumentoGuiadoEstado.extension= "";
    adjuntoDocumentoGuiadoEstado.nombreArchivo= "";
    adjuntoDocumentoGuiadoEstado.mime= "";
    actualizarVistaPreviaAdjuntoDocumentoGuiado("", "");
    document.querySelectorAll("#tiposAdjuntoDocumentoGuiado [data-tipo-adjunto]").forEach(function(boton) {
        boton.classList.remove("is-selected");
        boton.setAttribute("aria-pressed", "false");
    });
    document.getElementById("pasoDatosAdjuntoDocumento").hidden= true;
    document.getElementById("pasoArchivoAdjuntoDocumento").hidden= true;
    document.getElementById("resumenAdjuntoDocumentoGuiado").hidden= true;
    document.getElementById("btnConfirmarAdjuntoDocumentoGuiado").disabled= true;
    mostrarErrorAdjuntoDocumentoGuiado("");
}

function actualizarVistaPreviaAdjuntoDocumentoGuiado(archivo, extension) {
    var contenedor= document.getElementById("vistaPreviaAdjuntoDocumento");
    var imagen= document.getElementById("imagenVistaPreviaAdjuntoDocumento");
    var pdf= document.getElementById("pdfVistaPreviaAdjuntoDocumento");
    if (!contenedor || !imagen || !pdf) { return; }
    imagen.hidden= true;
    pdf.hidden= true;
    imagen.removeAttribute("src");
    pdf.removeAttribute("src");
    if (!archivo) {
        contenedor.hidden= true;
        return;
    }
    contenedor.hidden= false;
    if (extension == "pdf") {
        pdf.src= archivo;
        pdf.hidden= false;
    } else {
        imagen.src= archivo;
        imagen.hidden= false;
    }
}

function abrirAdjuntoDocumentoGuiado(origen) {
    var dialogo= document.getElementById("dialogoAdjuntoDocumentoGuiado");
    if (!dialogo) { return; }
    adjuntoDocumentoGuiadoEstado.origen= origen == "gasto" ? "gasto" : "hilo";
    adjuntoDocumentoGuiadoEstado.hilo= String(cod_interConsulta || "");
    adjuntoDocumentoGuiadoEstado.tipoMovimiento= adjuntoDocumentoGuiadoEstado.origen == "gasto"
        ? normalizarTipoMovimientoFinanciero(document.getElementById("inptTipoGasto") ? document.getElementById("inptTipoGasto").value : "Egreso")
        : "Egreso";
    adjuntoDocumentoGuiadoEstado.focoAnterior= document.activeElement;
    limpiarFormularioAdjuntoDocumentoGuiado();
    var fechaMovimiento= document.getElementById("inptFechaGasto");
    if (origen == "gasto" && fechaMovimiento && fechaMovimiento.value) {
        document.getElementById("fechaAdjuntoDocumento").value= fechaMovimiento.value;
    }
    document.getElementById("etiquetaAdjuntoDocumentoGuiado").textContent= origen == "gasto" ? "Respaldo del movimiento" : "Respaldo del Hilo";
    document.getElementById("tituloAdjuntoDocumentoGuiado").textContent= origen == "gasto" ? "Preparar comprobante" : "Adjuntar archivo o imagen";
    document.getElementById("btnConfirmarAdjuntoDocumentoGuiado").innerHTML= origen == "gasto"
        ? '<i class="fa-solid fa-check" aria-hidden="true"></i> Usar en el movimiento'
        : '<i class="fa-solid fa-paperclip" aria-hidden="true"></i> Adjuntar al Hilo';
    dialogo.hidden= false;
    document.body.classList.add("adjunto-documento-dialog-open");
    configurarTiposAdjuntoDocumentoGuiado();
    cargarContextoAdjuntoDocumentoGuiado();
    setTimeout(function() {
        var titulo= document.getElementById("tituloAdjuntoDocumentoGuiado");
        if (titulo) { titulo.focus(); }
    }, 0);
}

function cerrarAdjuntoDocumentoGuiado(conservarEstado) {
    if (adjuntoDocumentoGuiadoEstado.guardando) { return; }
    var dialogo= document.getElementById("dialogoAdjuntoDocumentoGuiado");
    if (dialogo) { dialogo.hidden= true; }
    document.body.classList.remove("adjunto-documento-dialog-open");
    var foco= adjuntoDocumentoGuiadoEstado.focoAnterior;
    if (!conservarEstado) { limpiarFormularioAdjuntoDocumentoGuiado(); }
    if (foco && typeof foco.focus == "function") {
        setTimeout(function() { foco.focus(); }, 0);
    }
}

function seleccionarTipoAdjuntoDocumentoGuiado(tipo) {
    tipo= ["factura", "comprobante", "otro"].indexOf(tipo) >= 0 ? tipo : "otro";
    var boton= document.querySelector('#tiposAdjuntoDocumentoGuiado [data-tipo-adjunto="' + tipo + '"]');
    if (!boton || boton.hidden) {
        mostrarErrorAdjuntoDocumentoGuiado("No tiene permiso para registrar este tipo de documento en el contexto actual.");
        return;
    }
    if (adjuntoDocumentoGuiadoEstado.tipo && adjuntoDocumentoGuiadoEstado.tipo != tipo) {
        adjuntoDocumentoGuiadoEstado.lecturaArchivo++;
        if (adjuntoDocumentoGuiadoEstado.archivo || document.getElementById("archivoAdjuntoDocumento").value) {
            adjuntoDocumentoGuiadoEstado.archivo= "";
            adjuntoDocumentoGuiadoEstado.extension= "";
            adjuntoDocumentoGuiadoEstado.nombreArchivo= "";
            document.getElementById("archivoAdjuntoDocumento").value= "";
            document.getElementById("nombreArchivoAdjuntoDocumento").textContent= "Seleccione nuevamente el archivo para el tipo elegido.";
        }
    }
    adjuntoDocumentoGuiadoEstado.tipo= tipo;
    document.querySelectorAll("#tiposAdjuntoDocumentoGuiado [data-tipo-adjunto]").forEach(function(item) {
        var seleccionado= item.getAttribute("data-tipo-adjunto") == tipo;
        item.classList.toggle("is-selected", seleccionado);
        item.setAttribute("aria-pressed", seleccionado ? "true" : "false");
    });
    var esFinanciero= tipo == "factura" || tipo == "comprobante";
    document.getElementById("pasoDatosAdjuntoDocumento").hidden= !esFinanciero;
    document.getElementById("camposFacturaAdjuntoDocumento").hidden= tipo != "factura";
    document.getElementById("camposDocumentoFiscalAdjunto").hidden= !esFinanciero;
    document.getElementById("grupoDescripcionAdjuntoDocumento").hidden= tipo != "comprobante";
    document.getElementById("avisoConceptoAdjuntoDocumento").hidden= !esFinanciero;
    document.getElementById("etiquetaNumeroAdjuntoDocumento").textContent= tipo == "comprobante" ? "N.º de recibo / comprobante (opcional)" : "N.º de factura (opcional)";
    document.getElementById("ayudaDatosAdjuntoDocumento").textContent= tipo == "factura"
        ? "Seleccione un proveedor o ingrese su razón social y RUC."
        : "Registre fecha, monto y una descripción breve. El número es opcional.";
    document.getElementById("pasoArchivoAdjuntoDocumento").hidden= false;
    document.getElementById("numeroPasoArchivoAdjuntoDocumento").textContent= esFinanciero ? "3" : "2";
    mostrarErrorAdjuntoDocumentoGuiado("");
    actualizarResumenAdjuntoDocumentoGuiado();
}

function cambiarProveedorAdjuntoDocumentoGuiado() {
    var proveedor= obtenerProveedorAdjuntoDocumentoGuiado();
    var razon= document.getElementById("razonSocialAdjuntoDocumento");
    var ruc= document.getElementById("rucAdjuntoDocumento");
    if (proveedor) {
        razon.value= proveedor.nombre_persona || "";
        ruc.value= proveedor.rut_proveedor || "";
        razon.readOnly= true;
        ruc.readOnly= !!proveedor.rut_proveedor;
        if (!proveedor.rut_proveedor) {
            setTimeout(function() { ruc.focus(); }, 0);
        }
    } else {
        razon.value= "";
        ruc.value= "";
        razon.readOnly= false;
        ruc.readOnly= false;
        setTimeout(function() { razon.focus(); }, 0);
    }
    actualizarResumenAdjuntoDocumentoGuiado();
}

function leerArchivoAdjuntoDocumentoGuiado(input) {
    mostrarErrorAdjuntoDocumentoGuiado("");
    var lecturaArchivo= ++adjuntoDocumentoGuiadoEstado.lecturaArchivo;
    adjuntoDocumentoGuiadoEstado.archivo= "";
    adjuntoDocumentoGuiadoEstado.extension= "";
    adjuntoDocumentoGuiadoEstado.nombreArchivo= "";
    adjuntoDocumentoGuiadoEstado.mime= "";
    actualizarVistaPreviaAdjuntoDocumentoGuiado("", "");
    var nombreArchivo= document.getElementById("nombreArchivoAdjuntoDocumento");
    if (!adjuntoDocumentoGuiadoEstado.tipo) {
        input.value= "";
        if (nombreArchivo) { nombreArchivo.textContent= "Todavia no se selecciono un archivo."; }
        actualizarResumenAdjuntoDocumentoGuiado();
        mostrarErrorAdjuntoDocumentoGuiado("Seleccione primero el tipo de adjunto.");
        return;
    }
    var archivo= input.files && input.files[0] ? input.files[0] : null;
    if (!archivo) {
        if (nombreArchivo) { nombreArchivo.textContent= "Todavia no se selecciono un archivo."; }
        actualizarResumenAdjuntoDocumentoGuiado();
        return;
    }
    if (nombreArchivo) { nombreArchivo.textContent= "Validando " + archivo.name + "..."; }
    actualizarResumenAdjuntoDocumentoGuiado();
    var extension= (archivo.name.split(".").pop() || "").toLowerCase();
    var permitidas= ["jpg", "jpeg", "png", "webp", "gif", "pdf"];
    if (permitidas.indexOf(extension) < 0) {
        input.value= "";
        if (nombreArchivo) { nombreArchivo.textContent= "Todavia no se selecciono un archivo."; }
        actualizarResumenAdjuntoDocumentoGuiado();
        mostrarErrorAdjuntoDocumentoGuiado("Seleccione una imagen JPG, PNG, WEBP, GIF o un documento PDF.");
        return;
    }
    if (archivo.size > 10000000) {
        input.value= "";
        if (nombreArchivo) { nombreArchivo.textContent= "Todavia no se selecciono un archivo."; }
        actualizarResumenAdjuntoDocumentoGuiado();
        mostrarErrorAdjuntoDocumentoGuiado("El archivo supera el límite de 10 MB.");
        return;
    }
    var lector= new FileReader();
    lector.onerror= function() {
        if (lecturaArchivo != adjuntoDocumentoGuiadoEstado.lecturaArchivo) { return; }
        input.value= "";
        if (nombreArchivo) { nombreArchivo.textContent= "No se pudo leer el archivo."; }
        actualizarResumenAdjuntoDocumentoGuiado();
        mostrarErrorAdjuntoDocumentoGuiado("No se pudo leer el archivo seleccionado.");
    };
    lector.onload= function(evento) {
        if (lecturaArchivo != adjuntoDocumentoGuiadoEstado.lecturaArchivo) { return; }
        adjuntoDocumentoGuiadoEstado.archivo= evento.target.result || "";
        adjuntoDocumentoGuiadoEstado.extension= extension == "jpeg" ? "jpg" : extension;
        adjuntoDocumentoGuiadoEstado.nombreArchivo= archivo.name;
        adjuntoDocumentoGuiadoEstado.mime= archivo.type || "";
        if (nombreArchivo) { nombreArchivo.textContent= archivo.name + " · " + Math.max(1, Math.round(archivo.size / 1024)) + " KB"; }
        actualizarVistaPreviaAdjuntoDocumentoGuiado(adjuntoDocumentoGuiadoEstado.archivo, adjuntoDocumentoGuiadoEstado.extension);
        actualizarResumenAdjuntoDocumentoGuiado();
    };
    lector.readAsDataURL(archivo);
}

function obtenerDatosAdjuntoDocumentoGuiado() {
    var proveedor= obtenerProveedorAdjuntoDocumentoGuiado();
    return {
        tipo_contraparte: proveedor ? "proveedor" : "otro",
        cod_proveedor: proveedor ? proveedor.cod_proveedor : 0,
        nombre_contraparte: document.getElementById("razonSocialAdjuntoDocumento").value.trim(),
        documento_contraparte: document.getElementById("rucAdjuntoDocumento").value.trim(),
        numero_factura: document.getElementById("numeroAdjuntoDocumento").value.trim(),
        fecha_emision: document.getElementById("fechaAdjuntoDocumento").value,
        importe_total: normalizarMontoAdjuntoDocumentoGuiado(document.getElementById("montoAdjuntoDocumento").value),
        observaciones: document.getElementById("descripcionAdjuntoDocumento").value.trim()
    };
}

function validarAdjuntoDocumentoGuiado(silencioso) {
    var tipo= adjuntoDocumentoGuiadoEstado.tipo;
    var datos= obtenerDatosAdjuntoDocumentoGuiado();
    var error= "";
    if (!tipo) {
        error= "Seleccione el tipo de adjunto.";
    } else if (tipo == "factura" && !datos.nombre_contraparte) {
        error= "Seleccione un proveedor o ingrese la razón social.";
    } else if (tipo == "factura" && !datos.documento_contraparte) {
        error= "Ingrese el RUC de la factura.";
    } else if ((tipo == "factura" || tipo == "comprobante") && !datos.fecha_emision) {
        error= "Seleccione la fecha del documento.";
    } else if ((tipo == "factura" || tipo == "comprobante") && datos.importe_total <= 0) {
        error= "Ingrese un monto mayor a cero.";
    } else if (tipo == "comprobante" && !datos.observaciones) {
        error= "Ingrese una descripción breve del recibo.";
    } else if (!adjuntoDocumentoGuiadoEstado.archivo || !adjuntoDocumentoGuiadoEstado.extension) {
        error= "Seleccione el archivo que desea adjuntar.";
    }
    if (!silencioso) { mostrarErrorAdjuntoDocumentoGuiado(error); }
    return {ok: error == "", mensaje: error, datos: datos};
}

function actualizarResumenAdjuntoDocumentoGuiado() {
    var resumen= document.getElementById("resumenAdjuntoDocumentoGuiado");
    var boton= document.getElementById("btnConfirmarAdjuntoDocumentoGuiado");
    var validacion= validarAdjuntoDocumentoGuiado(true);
    boton.disabled= !validacion.ok || adjuntoDocumentoGuiadoEstado.guardando;
    if (!adjuntoDocumentoGuiadoEstado.tipo) {
        resumen.hidden= true;
        return;
    }
    var tipo= adjuntoDocumentoGuiadoEstado.tipo;
    var preparado= validacion.ok;
    var nombreTipoResumen= tipo == "factura" ? "Factura" : (tipo == "comprobante" ? "Recibo" : "Adjunto simple");
    var titulo= nombreTipoResumen + (preparado ? " preparado" : " en preparacion");
    var partes= [];
    if (tipo == "factura" && validacion.datos.nombre_contraparte) { partes.push(validacion.datos.nombre_contraparte); }
    if ((tipo == "factura" || tipo == "comprobante") && validacion.datos.numero_factura) { partes.push("N.º " + validacion.datos.numero_factura); }
    if ((tipo == "factura" || tipo == "comprobante") && validacion.datos.importe_total > 0) { partes.push("Gs. " + formatearMontoAdjuntoDocumentoGuiado(validacion.datos.importe_total)); }
    if (adjuntoDocumentoGuiadoEstado.nombreArchivo) { partes.push(adjuntoDocumentoGuiadoEstado.nombreArchivo); }
    if (!preparado && validacion.mensaje) { partes.push(validacion.mensaje); }
    resumen.classList.toggle("is-ready", preparado);
    resumen.innerHTML= "<strong>" + escaparHtmlAdjuntoDocumentoGuiado(titulo) + "</strong>" + escaparHtmlAdjuntoDocumentoGuiado(partes.length ? partes.join(" · ") : "Complete los datos y seleccione el archivo.");
    resumen.hidden= false;
}

function aplicarAdjuntoDocumentoGuiadoAGasto(validacion) {
    tipoAdjuntoDocumentoGasto= adjuntoDocumentoGuiadoEstado.tipo;
    datosAdjuntoDocumentoGasto= validacion.datos;
    nombreArchivoAdjuntoDocumentoGasto= adjuntoDocumentoGuiadoEstado.nombreArchivo || "";
    fotoGasto= adjuntoDocumentoGuiadoEstado.archivo;
    extGasto= adjuntoDocumentoGuiadoEstado.extension;
    var miniatura= document.getElementById("imgfotoGasto");
    if (miniatura) {
        var esImagen= ["jpg", "jpeg", "png", "webp", "gif"].indexOf(extGasto) >= 0;
        miniatura.style.backgroundImage= esImagen
            ? "url(" + fotoGasto + ")"
            : "url('/GoodVentaAsisCap/iconos/informedevolucion.png')";
        miniatura.classList.toggle("imgFotoProductoDocumento", !esImagen);
    }
    renderizarResumenAdjuntoMovimientoFinanciero();
    cerrarAdjuntoDocumentoGuiado(false);
}

function renderizarResumenAdjuntoMovimientoFinanciero() {
    var resumen= document.getElementById("resumenAdjuntoMovimientoFinanciero");
    var titulo= document.getElementById("tituloAdjuntoMovimientoFinanciero");
    var ayuda= document.getElementById("ayudaAdjuntoMovimientoFinanciero");
    if (!resumen || !titulo || !ayuda) { return; }
    if (!fotoGasto || !extGasto) {
        resumen.hidden= true;
        titulo.textContent= "Adjuntar comprobante";
        var tipoMovimiento= normalizarTipoMovimientoFinanciero(document.getElementById("inptTipoGasto") ? document.getElementById("inptTipoGasto").value : "Egreso");
        ayuda.textContent= tipoMovimiento == "Egreso"
            ? "Identifique primero si es factura, recibo o imagen."
            : (tipoMovimiento == "Deposito" ? "Adjunte el comprobante general del depósito." : "Adjunte un comprobante o imagen del ingreso.");
        return;
    }
    var nombreTipo= tipoAdjuntoDocumentoGasto == "factura" ? "Factura" : (tipoAdjuntoDocumentoGasto == "comprobante" ? "Recibo" : "Comprobante");
    titulo.textContent= nombreTipo + " preparado";
    ayuda.textContent= nombreArchivoAdjuntoDocumentoGasto || ("Archivo ." + extGasto);
    var detalle= [];
    if (datosAdjuntoDocumentoGasto && datosAdjuntoDocumentoGasto.numero_factura) { detalle.push("N.º " + datosAdjuntoDocumentoGasto.numero_factura); }
    if (datosAdjuntoDocumentoGasto && datosAdjuntoDocumentoGasto.importe_total) { detalle.push("Gs. " + formatearMontoAdjuntoDocumentoGuiado(datosAdjuntoDocumentoGasto.importe_total)); }
    resumen.innerHTML= "<span><strong>" + escaparHtmlAdjuntoDocumentoGuiado(nombreTipo) + " listo para guardar</strong>" + (detalle.length ? " · " + escaparHtmlAdjuntoDocumentoGuiado(detalle.join(" · ")) : "") + ". Se registrará junto con el movimiento.</span>"
        + '<button type="button" onclick="limpiarAdjuntoMovimientoFinancieroGuiado(true)">Quitar adjunto</button>';
    resumen.hidden= false;
}

function limpiarAdjuntoMovimientoFinancieroGuiado(restaurarMiniatura) {
    tipoAdjuntoDocumentoGasto= "otro";
    datosAdjuntoDocumentoGasto= null;
    nombreArchivoAdjuntoDocumentoGasto= "";
    fotoGasto= "";
    extGasto= "";
    if (restaurarMiniatura !== false) {
        var miniatura= document.getElementById("imgfotoGasto");
        if (miniatura) {
            miniatura.style.backgroundImage= "url('/GoodVentaAsisCap/iconos/imagenphoto.png')";
            miniatura.classList.remove("imgFotoProductoDocumento");
        }
    }
    renderizarResumenAdjuntoMovimientoFinanciero();
}

function manejarCambioTipoMovimientoAdjuntoGuiado() {
    var tipoMovimiento= normalizarTipoMovimientoFinanciero(document.getElementById("inptTipoGasto") ? document.getElementById("inptTipoGasto").value : "Egreso");
    if (tipoMovimiento != "Egreso" && (tipoAdjuntoDocumentoGasto == "factura" || tipoAdjuntoDocumentoGasto == "comprobante")) {
        limpiarAdjuntoMovimientoFinancieroGuiado(true);
        ver_vetana_informativa("Adjunto retirado", "Al cambiar el movimiento a " + tipoMovimiento.toLowerCase() + ", la factura o recibo dejó de ser válido. Vuelva a adjuntar un comprobante general.", "advertencia");
    } else {
        renderizarResumenAdjuntoMovimientoFinanciero();
    }
}

function confirmarAdjuntoDocumentoGuiado() {
    var validacion= validarAdjuntoDocumentoGuiado(false);
    if (!validacion.ok || adjuntoDocumentoGuiadoEstado.guardando) { return; }
    if (adjuntoDocumentoGuiadoEstado.origen == "gasto") {
        aplicarAdjuntoDocumentoGuiadoAGasto(validacion);
        return;
    }
    enviarAdjuntoDocumentoGuiadoHilo(validacion);
}

function enviarAdjuntoDocumentoGuiadoHilo(validacion) {
    var hiloSolicitado= String(adjuntoDocumentoGuiadoEstado.hilo || cod_interConsulta || "");
    var contenido= document.getElementById("inptContenidoAbmMensaje").innerHTML;
    var codDictamen= document.getElementById("dictamenAbmMensaje").value;
    var respuestaCitada= document.getElementById("codMensajeRespuestaInterConsulta");
    obtener_datos_user();
    var datos= new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "nuevo mensaje con adjunto");
    datos.append("cod_interConsulta", hiloSolicitado);
    datos.append("contenido", contenido || "");
    datos.append("cod_dictamenFK", codDictamen || "");
    datos.append("cod_mensaje_respuestaFK", respuestaCitada && respuestaCitada.value ? respuestaCitada.value : "");
    datos.append("tipo_adjunto", adjuntoDocumentoGuiadoEstado.tipo);
    datos.append("foto", adjuntoDocumentoGuiadoEstado.archivo);
    datos.append("ext", adjuntoDocumentoGuiadoEstado.extension);
    datos.append("nombre_archivo", adjuntoDocumentoGuiadoEstado.nombreArchivo);
    datos.append("datos_documento", JSON.stringify(validacion.datos || {}));
    adjuntoDocumentoGuiadoEstado.guardando= true;
    document.getElementById("btnConfirmarAdjuntoDocumentoGuiado").disabled= true;
    document.getElementById("dialogoAdjuntoDocumentoGuiado").setAttribute("aria-busy", "true");
    mostrarErrorAdjuntoDocumentoGuiado("");
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
            mostrarErrorAdjuntoDocumentoGuiado("No se pudo enviar el adjunto. Los datos se conservaron para reintentar.");
        },
        success: function(responseText) {
            try {
                var respuesta= $.parseJSON(responseText);
                if (respuesta["1"] != "exito") {
                    throw new Error(mensajeRespuestaSeguimientoInterConsulta(respuesta, "Revise los datos e intente nuevamente."));
                }
                adjuntoDocumentoGuiadoEstado.guardando= false;
                cerrarAdjuntoDocumentoGuiado(false);
                if (String(cod_interConsulta) === hiloSolicitado) {
                    limpiarCamposDetallesInterConsulta();
                    buscarInterConsultasYContenido(hiloSolicitado);
                    limpiarcamposMensaje();
                }
                var nombreDocumento= respuesta.tipo_adjunto == "factura" ? "Factura" : (respuesta.tipo_adjunto == "comprobante" ? "Recibo" : "Adjunto");
                ver_vetana_informativa(nombreDocumento + " guardado", respuesta.centro_facturas ? "El documento también quedó registrado en el Centro de Facturas y Documentos." : "El archivo quedó registrado en el Hilo.", "info");
                if (respuesta.centro_facturas && typeof centroFacturasActualizarBadge == "function") {
                    centroFacturasActualizarBadge();
                }
            } catch (error) {
                mostrarErrorAdjuntoDocumentoGuiado(error.message || "No se pudo guardar el adjunto.");
            }
        },
        complete: function() {
            adjuntoDocumentoGuiadoEstado.guardando= false;
            document.getElementById("dialogoAdjuntoDocumentoGuiado").removeAttribute("aria-busy");
            actualizarResumenAdjuntoDocumentoGuiado();
            verCerrarEfectoCargando("");
        }
    });
}

document.addEventListener("keydown", function(evento) {
    var dialogo= document.getElementById("dialogoAdjuntoDocumentoGuiado");
    if (!dialogo || dialogo.hidden) { return; }
    if (evento.key == "Escape") {
        evento.preventDefault();
        cerrarAdjuntoDocumentoGuiado();
        return;
    }
    if (evento.key != "Tab") { return; }
    var elementos= Array.prototype.slice.call(dialogo.querySelectorAll('button:not([disabled]):not([hidden]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'))
        .filter(function(elemento) { return elemento.offsetParent !== null; });
    if (!elementos.length) { return; }
    var primero= elementos[0];
    var ultimo= elementos[elementos.length - 1];
    if (elementos.indexOf(document.activeElement) < 0) {
        evento.preventDefault();
        (evento.shiftKey ? ultimo : primero).focus();
        return;
    }
    if (evento.shiftKey && document.activeElement === primero) {
        evento.preventDefault();
        ultimo.focus();
    } else if (!evento.shiftKey && document.activeElement === ultimo) {
        evento.preventDefault();
        primero.focus();
    }
});

function verificarCamposMensaje() {
    const editor= document.getElementById('inptContenidoAbmMensaje');
    const contenido= editor.innerHTML;
    const cod_dictamenFK= document.getElementById('dictamenAbmMensaje').value;
    if (!String(editor.textContent || "").replace(/\u00a0/g, " ").trim()) {
        ver_vetana_informativa("Falto ingresar un contenido");
        return false;
    }

    // Deshabilita temporalmente el boton de enviar
    document.getElementById('btnEnviarContenidoAbmMensaje').disabled= true;

    abmMensaje("", contenido, cod_dictamenFK);
}

function abmMensaje(fecha, contenido, cod_dictamenFK) {
    var hiloSolicitado= String(cod_interConsulta || "");
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
                        if (String(cod_interConsulta) === hiloSolicitado) {
                            limpiarCamposDetallesInterConsulta();
                            buscarInterConsultasYContenido(hiloSolicitado);
                            limpiarcamposMensaje();
                        }
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
    datos.append("cod_interConsulta", hiloSolicitado);
    
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
    cancelarRespuestaCitadaInterConsulta(false);
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
                    ver_vetana_informativa(nombreDocumentoRegistrado + " registrado", "El adjunto ya esta disponible en el Centro de Facturas y Documentos.", "info");
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
                    actualizarAvatarStackDetalleHilo(datosHiloRespuesta.participantes_actuales || []);

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
                    actualizarVistaInterConsultaLeida();
                    actualizarCuentaRegresivaSeguimientosInterConsulta();
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

function agregarCallbackPanelDictamenInterConsulta(tarjeta, callback) {
    if (!tarjeta || typeof callback != "function") { return; }
    if (!Array.isArray(tarjeta._callbacksCargaDictamenInterConsulta)) {
        tarjeta._callbacksCargaDictamenInterConsulta= [];
    }
    tarjeta._callbacksCargaDictamenInterConsulta.push(callback);
}

function resolverCallbacksPanelDictamenInterConsulta(tarjeta, error) {
    if (!tarjeta || !Array.isArray(tarjeta._callbacksCargaDictamenInterConsulta)) { return; }
    var callbacks= tarjeta._callbacksCargaDictamenInterConsulta.slice();
    tarjeta._callbacksCargaDictamenInterConsulta= [];
    callbacks.forEach(function(callback) {
        callback(error || null);
    });
}

function cargarPanelDictamenInterConsulta(codDictamen, idPanelResolucion, idPanelMensajes, boton, despuesDeCargar) {
    const panelObjetivoId= boton ? boton.getAttribute("aria-controls") : idPanelResolucion;
    const panelObjetivo= document.getElementById(panelObjetivoId);
    const panelResolucion= document.getElementById(idPanelResolucion);
    const panelMensajes= document.getElementById(idPanelMensajes);
    const tarjeta= panelObjetivo ? panelObjetivo.closest(".interc-dictamen-card") : null;
    if (!panelObjetivo || !panelResolucion || !panelMensajes || !tarjeta) {
        if (typeof despuesDeCargar == "function") {
            despuesDeCargar(new Error("No se encontro el panel del dictamen."));
        }
        return;
    }
    agregarCallbackPanelDictamenInterConsulta(tarjeta, despuesDeCargar);

    if (tarjeta.dataset.detalleCargado == "1") {
        alternarPanelDictamenInterConsulta(panelObjetivoId, boton);
        resolverCallbacksPanelDictamenInterConsulta(tarjeta, null);
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
                resolverCallbacksPanelDictamenInterConsulta(tarjeta, new Error("No se pudo cargar la resolucion del mensaje original."));
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
                setTimeout(function() {
                    resolverCallbacksPanelDictamenInterConsulta(tarjeta, null);
                }, 180);
            } catch (error) {
                const estado= panelObjetivo.querySelector(".interconsulta-flow-state");
                if (estado) {
                    estado.classList.add("interconsulta-flow-state--error");
                    estado.textContent= "No se pudo cargar el dictamen.";
                }
                resolverCallbacksPanelDictamenInterConsulta(tarjeta, error);
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
    var hiloSolicitado= String(cod_interConsulta || "");
    if (!hiloSolicitado) { return; }
    obtener_datos_user()
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", "buscarMasInterConsultasYContenido");
    datos.append("cod_interConsulta", hiloSolicitado);
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
                        btnMasMensajes= htmlBotonMasMensajesInterconsulta(siguienteOffset, cod_dictamen || "");
                    }
                    
                    if (listaMensajes) {
                        listaMensajes.innerHTML = datos["2"] + listaMensajes.innerHTML;
                        if (btnMasMensajes) {
                            panelMensajes.insertAdjacentHTML("afterbegin", btnMasMensajes);
                        }
                    } else {
                        panelMensajes.innerHTML = btnMasMensajes + datos["2"] + panelMensajes.innerHTML;
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
    cancelarRespuestaCitadaInterConsulta(false);
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
        inicializarResumenPeriodicoHilosInterConsulta();
        actualizarResumenHilosInterConsulta();

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

var miniHiloLaboratorioPopoverInterConsulta= null;
var miniHiloLaboratorioTriggerInterConsulta= null;
var miniHiloLaboratorioViewerInterConsulta= null;

function escaparMiniHiloLaboratorioInterConsulta(valor) {
    return (valor === null || typeof valor === "undefined" ? "" : String(valor))
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function textoMiniHiloLaboratorioInterConsulta(valor, alternativo) {
    var texto= valor === null || typeof valor === "undefined" ? "" : String(valor).trim();
    if (texto) {
        return texto;
    }
    if (arguments.length > 1) {
        return alternativo === null || typeof alternativo === "undefined"
            ? "" : String(alternativo);
    }
    return "No asignado";
}

function fechaMiniHiloLaboratorioInterConsulta(valor, incluirHora) {
    var texto= textoMiniHiloLaboratorioInterConsulta(valor, "");
    var coincidencia= texto.match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}))?/);
    if (!coincidencia) {
        return texto || "Sin fecha definida";
    }
    return coincidencia[3] + "/" + coincidencia[2] + "/" + coincidencia[1]
        + (incluirHora && coincidencia[4] ? " · " + coincidencia[4] + ":" + coincidencia[5] : "");
}

function nombreEstadoMiniHiloLaboratorioInterConsulta(nodo) {
    var nombre= textoMiniHiloLaboratorioInterConsulta(nodo.estado_nombre, "");
    if (nombre) {
        return nombre;
    }
    nombre= textoMiniHiloLaboratorioInterConsulta(nodo.estado, "Registrado");
    return nombre.replace(/_/g, " ").replace(/\b\w/g, function (letra) {
        return letra.toUpperCase();
    });
}

function campoModificadoMiniHiloLaboratorioInterConsulta(nodo, claves) {
    var modificados= Array.isArray(nodo.campos_modificados) ? nodo.campos_modificados : [];
    return claves.some(function (clave) {
        return modificados.indexOf(clave) >= 0;
    });
}

function campoMiniHiloLaboratorioInterConsulta(etiqueta, valor, modificado) {
    return '<div class="interconsulta-lab-node-field' + (modificado ? ' is-modified' : '') + '">'
        + '<small>' + escaparMiniHiloLaboratorioInterConsulta(etiqueta)
        + (modificado ? ' <i class="fa-solid fa-pen" aria-hidden="true"></i>' : '') + '</small>'
        + '<strong>' + escaparMiniHiloLaboratorioInterConsulta(
            textoMiniHiloLaboratorioInterConsulta(valor, "No asignado")
        ) + '</strong></div>';
}

function mediaMiniHiloLaboratorioInterConsulta(nodo) {
    var media= Array.isArray(nodo.media) ? nodo.media : [];
    if (!media.length) {
        return '<span class="interconsulta-lab-node-media-empty"><i class="fa-solid fa-image" aria-hidden="true"></i>'
            + 'Este nodo no incorporó fotografías ni documentos</span>';
    }
    return '<div class="interconsulta-lab-node-media" aria-label="Archivos incorporados en este nodo">'
        + media.map(function (archivo) {
            var id= parseInt(archivo.id, 10) || 0;
            var mime= textoMiniHiloLaboratorioInterConsulta(archivo.mime, "").toLowerCase();
            var nombre= textoMiniHiloLaboratorioInterConsulta(
                archivo.descripcion || archivo.nombre,
                mime === "application/pdf" ? "Documento PDF" : "Fotografía"
            );
            var vista= mime === "application/pdf"
                ? '<i class="fa-solid fa-file-pdf" aria-hidden="true"></i>'
                : '<span class="interconsulta-lab-node-media__loading"><i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i></span>';
            return '<button type="button" data-interconsulta-lab-media="' + id + '" '
                + 'data-interconsulta-lab-media-mime="' + escaparMiniHiloLaboratorioInterConsulta(mime) + '" '
                + 'data-interconsulta-lab-media-name="' + escaparMiniHiloLaboratorioInterConsulta(nombre) + '" '
                + 'aria-label="Abrir ' + escaparMiniHiloLaboratorioInterConsulta(nombre) + '">'
                + vista + '<small>' + escaparMiniHiloLaboratorioInterConsulta(nombre) + '</small></button>';
        }).join("") + '</div>';
}

function contenidoNodoMiniHiloLaboratorioInterConsulta(nodo) {
    var snapshot= nodo.snapshot && typeof nodo.snapshot === "object" ? nodo.snapshot : null;
    var actor= nodo.actor && typeof nodo.actor === "object" ? nodo.actor : {};
    var responsable= nodo.responsable && typeof nodo.responsable === "object"
        ? nodo.responsable : actor;
    var avatar= textoMiniHiloLaboratorioInterConsulta(responsable.avatar || actor.avatar, "");
    var cabeceraActor= '<div class="interconsulta-lab-node-actor">'
        + (avatar
            ? '<img src="' + escaparMiniHiloLaboratorioInterConsulta(avatar) + '" alt="">'
            : '<span aria-hidden="true"><i class="fa-solid fa-user"></i></span>')
        + '<div><strong>' + escaparMiniHiloLaboratorioInterConsulta(
            textoMiniHiloLaboratorioInterConsulta(responsable.nombre || actor.nombre, "Usuario Telar")
        ) + '</strong><small>Responsable del nodo'
        + (responsable.rol ? ' · ' + escaparMiniHiloLaboratorioInterConsulta(responsable.rol) : '')
        + '</small></div></div>';
    var resumen= '<dl class="interconsulta-lab-node-summary">'
        + '<div><dt>Fecha y hora</dt><dd>'
        + escaparMiniHiloLaboratorioInterConsulta(fechaMiniHiloLaboratorioInterConsulta(nodo.fecha, true))
        + '</dd></div><div><dt>Local</dt><dd>'
        + escaparMiniHiloLaboratorioInterConsulta(
            textoMiniHiloLaboratorioInterConsulta(nodo.local, "Sin local informado")
        ) + '</dd></div></dl>';
    if (nodo.cierre) {
        return cabeceraActor + resumen
            + '<div class="interconsulta-lab-node-closure"><i class="fa-solid fa-flag-checkered" aria-hidden="true"></i>'
            + '<div><strong>' + escaparMiniHiloLaboratorioInterConsulta(
                nodo.estado === "cancelado" ? "Seguimiento cancelado" : "Resultado finalizado"
            ) + '</strong><span>' + escaparMiniHiloLaboratorioInterConsulta(
                textoMiniHiloLaboratorioInterConsulta(nodo.observacion, "El hilo quedó cerrado.")
            ) + '</span></div></div>';
    }
    var campos= "";
    if (snapshot) {
        campos= '<div class="interconsulta-lab-node-fields">'
            + campoMiniHiloLaboratorioInterConsulta(
                "Tipo de trabajo",
                snapshot.tipo_trabajo,
                campoModificadoMiniHiloLaboratorioInterConsulta(nodo, ["cod_tipo_trabajo"])
            )
            + campoMiniHiloLaboratorioInterConsulta(
                "Colorimetría",
                snapshot.colorimetro,
                campoModificadoMiniHiloLaboratorioInterConsulta(nodo, ["colorimetro"])
            )
            + campoMiniHiloLaboratorioInterConsulta("Paciente", snapshot.paciente, false)
            + campoMiniHiloLaboratorioInterConsulta("Producto de la venta", snapshot.producto, false)
            + campoMiniHiloLaboratorioInterConsulta(
                "Doctor",
                snapshot.doctor,
                campoModificadoMiniHiloLaboratorioInterConsulta(nodo, ["cod_especialista"])
            )
            + campoMiniHiloLaboratorioInterConsulta(
                "Mecánico dental",
                snapshot.mecanico_dental,
                campoModificadoMiniHiloLaboratorioInterConsulta(
                    nodo,
                    ["cod_mecanico_dental", "cod_tecnico_usuario"]
                )
            )
            + campoMiniHiloLaboratorioInterConsulta(
                "Retiro",
                snapshot.fecha_retiro
                    ? fechaMiniHiloLaboratorioInterConsulta(snapshot.fecha_retiro, false)
                    : "Sin fecha definida",
                campoModificadoMiniHiloLaboratorioInterConsulta(nodo, ["fecha_retiro"])
            )
            + campoMiniHiloLaboratorioInterConsulta(
                "Entrega",
                snapshot.fecha_entrega
                    ? fechaMiniHiloLaboratorioInterConsulta(snapshot.fecha_entrega, false)
                    : "Sin fecha definida",
                campoModificadoMiniHiloLaboratorioInterConsulta(nodo, ["fecha_entrega"])
            )
            + campoMiniHiloLaboratorioInterConsulta(
                "Costo",
                snapshot.costo_estimado === null || typeof snapshot.costo_estimado === "undefined"
                    ? "Sin registrar" : snapshot.costo_estimado,
                campoModificadoMiniHiloLaboratorioInterConsulta(nodo, ["costo_estimado"])
            )
            + campoMiniHiloLaboratorioInterConsulta(
                "Local",
                snapshot.local || nodo.local,
                campoModificadoMiniHiloLaboratorioInterConsulta(nodo, ["cod_local"])
            ) + '</div>';
    }
    var observacion= snapshot && snapshot.observacion
        ? snapshot.observacion : nodo.observacion;
    return cabeceraActor + mediaMiniHiloLaboratorioInterConsulta(nodo) + campos + resumen
        + '<div class="interconsulta-lab-node-note'
        + (campoModificadoMiniHiloLaboratorioInterConsulta(nodo, ["observacion"]) ? ' is-modified' : '')
        + '"><small>Observación'
        + (campoModificadoMiniHiloLaboratorioInterConsulta(nodo, ["observacion"])
            ? ' <i class="fa-solid fa-pen" aria-hidden="true"></i>' : '')
        + '</small><p>' + escaparMiniHiloLaboratorioInterConsulta(
            textoMiniHiloLaboratorioInterConsulta(observacion, "Sin observación registrada")
        ) + '</p></div>';
}

function posicionarNodoMiniHiloLaboratorioInterConsulta() {
    if (!miniHiloLaboratorioPopoverInterConsulta
        || !miniHiloLaboratorioTriggerInterConsulta
        || miniHiloLaboratorioPopoverInterConsulta.hidden) {
        return;
    }
    var rect= miniHiloLaboratorioTriggerInterConsulta.getBoundingClientRect();
    var popover= miniHiloLaboratorioPopoverInterConsulta;
    var margen= 12;
    var ancho= Math.min(410, window.innerWidth - (margen * 2));
    popover.style.width= ancho + "px";
    popover.style.left= Math.max(
        margen,
        Math.min(window.innerWidth - ancho - margen, rect.left + (rect.width / 2) - (ancho / 2))
    ) + "px";
    popover.style.top= (rect.bottom + 9) + "px";
    var alto= popover.offsetHeight;
    if (rect.bottom + 9 + alto > window.innerHeight - margen) {
        popover.style.top= Math.max(margen, rect.top - alto - 9) + "px";
    }
}

function cerrarNodoMiniHiloLaboratorioInterConsulta(devolverFoco) {
    var trigger= miniHiloLaboratorioTriggerInterConsulta;
    if (miniHiloLaboratorioPopoverInterConsulta) {
        miniHiloLaboratorioPopoverInterConsulta.hidden= true;
        miniHiloLaboratorioPopoverInterConsulta.innerHTML= "";
    }
    if (trigger) {
        trigger.setAttribute("aria-expanded", "false");
    }
    miniHiloLaboratorioTriggerInterConsulta= null;
    if (devolverFoco && trigger && document.documentElement.contains(trigger)) {
        trigger.focus();
    }
}

function cargarMiniaturasNodoLaboratorioInterConsulta(popover) {
    if (!popover || !window.TrabajoLaboratorio
        || typeof window.TrabajoLaboratorio.obtenerMedia !== "function") {
        return;
    }
    Array.prototype.forEach.call(
        popover.querySelectorAll('[data-interconsulta-lab-media]:not([data-interconsulta-lab-media-mime="application/pdf"])'),
        function (boton) {
            var id= boton.getAttribute("data-interconsulta-lab-media");
            window.TrabajoLaboratorio.obtenerMedia(id).then(function (media) {
                if (!document.documentElement.contains(boton)) {
                    return;
                }
                boton._interconsultaLabMedia= media;
                var vista= boton.querySelector(".interconsulta-lab-node-media__loading");
                if (vista) {
                    vista.className= "interconsulta-lab-node-media__preview";
                    vista.innerHTML= '<img src="' + escaparMiniHiloLaboratorioInterConsulta(media.src) + '" alt="">';
                }
                posicionarNodoMiniHiloLaboratorioInterConsulta();
            }).catch(function () {
                var vista= boton.querySelector(".interconsulta-lab-node-media__loading");
                if (vista) {
                    vista.innerHTML= '<i class="fa-solid fa-image" aria-hidden="true"></i>';
                }
            });
        }
    );
}

function abrirNodoMiniHiloLaboratorioInterConsulta(trigger) {
    var nodo;
    try {
        nodo= JSON.parse(trigger.getAttribute("data-interconsulta-lab-node") || "{}");
    } catch (error) {
        if (typeof ver_vetana_informativa === "function") {
            ver_vetana_informativa("No se pudo interpretar la información histórica de este nodo.");
        }
        return;
    }
    if (miniHiloLaboratorioTriggerInterConsulta === trigger
        && miniHiloLaboratorioPopoverInterConsulta
        && !miniHiloLaboratorioPopoverInterConsulta.hidden) {
        cerrarNodoMiniHiloLaboratorioInterConsulta(true);
        return;
    }
    cerrarNodoMiniHiloLaboratorioInterConsulta(false);
    if (!miniHiloLaboratorioPopoverInterConsulta) {
        miniHiloLaboratorioPopoverInterConsulta= document.createElement("div");
        miniHiloLaboratorioPopoverInterConsulta.id= "interconsultaLabNodePopover";
        miniHiloLaboratorioPopoverInterConsulta.className= "interconsulta-lab-node-popover";
        miniHiloLaboratorioPopoverInterConsulta.setAttribute("role", "dialog");
        miniHiloLaboratorioPopoverInterConsulta.setAttribute("aria-modal", "false");
        miniHiloLaboratorioPopoverInterConsulta.setAttribute("aria-label", "Detalle histórico del nodo");
        document.body.appendChild(miniHiloLaboratorioPopoverInterConsulta);
    }
    miniHiloLaboratorioTriggerInterConsulta= trigger;
    trigger.setAttribute("aria-expanded", "true");
    miniHiloLaboratorioPopoverInterConsulta.innerHTML=
        '<header class="interconsulta-lab-node-popover__header"><div><small>'
        + escaparMiniHiloLaboratorioInterConsulta(
            nodo.cierre ? "Resultado del seguimiento" : (nodo.version ? "Versión " + nodo.version : "Versión conservada")
        ) + '</small><h3>' + escaparMiniHiloLaboratorioInterConsulta(
            textoMiniHiloLaboratorioInterConsulta(nodo.titulo, "Evento registrado")
        ) + '</h3></div><span>' + escaparMiniHiloLaboratorioInterConsulta(
            nombreEstadoMiniHiloLaboratorioInterConsulta(nodo)
        ) + '</span><button type="button" data-interconsulta-lab-popover-close '
        + 'aria-label="Cerrar detalle"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></header>'
        + '<div class="interconsulta-lab-node-popover__body">'
        + contenidoNodoMiniHiloLaboratorioInterConsulta(nodo) + '</div>';
    miniHiloLaboratorioPopoverInterConsulta.hidden= false;
    posicionarNodoMiniHiloLaboratorioInterConsulta();
    cargarMiniaturasNodoLaboratorioInterConsulta(miniHiloLaboratorioPopoverInterConsulta);
}

function cerrarVisorMiniHiloLaboratorioInterConsulta() {
    if (miniHiloLaboratorioViewerInterConsulta) {
        miniHiloLaboratorioViewerInterConsulta.remove();
        miniHiloLaboratorioViewerInterConsulta= null;
    }
}

function mostrarVisorMiniHiloLaboratorioInterConsulta(media, titulo) {
    cerrarVisorMiniHiloLaboratorioInterConsulta();
    var esPdf= textoMiniHiloLaboratorioInterConsulta(media.mime, "").toLowerCase() === "application/pdf";
    miniHiloLaboratorioViewerInterConsulta= document.createElement("div");
    miniHiloLaboratorioViewerInterConsulta.className= "interconsulta-lab-media-viewer";
    miniHiloLaboratorioViewerInterConsulta.innerHTML=
        '<figure role="dialog" aria-modal="true" aria-label="Evidencia del trabajo">'
        + '<button type="button" data-interconsulta-lab-viewer-close aria-label="Cerrar evidencia">'
        + '<i class="fa-solid fa-xmark" aria-hidden="true"></i></button>'
        + (esPdf
            ? '<iframe sandbox referrerpolicy="no-referrer" src="'
                + escaparMiniHiloLaboratorioInterConsulta(media.src) + '" title="'
                + escaparMiniHiloLaboratorioInterConsulta(titulo) + '"></iframe>'
            : '<img src="' + escaparMiniHiloLaboratorioInterConsulta(media.src) + '" alt="'
                + escaparMiniHiloLaboratorioInterConsulta(titulo) + '">')
        + '<figcaption>' + escaparMiniHiloLaboratorioInterConsulta(titulo) + '</figcaption></figure>';
    document.body.appendChild(miniHiloLaboratorioViewerInterConsulta);
}

document.addEventListener("click", function (event) {
    var cerrarVisor= event.target.closest("[data-interconsulta-lab-viewer-close]");
    if (cerrarVisor || (miniHiloLaboratorioViewerInterConsulta
        && event.target === miniHiloLaboratorioViewerInterConsulta)) {
        event.preventDefault();
        cerrarVisorMiniHiloLaboratorioInterConsulta();
        return;
    }
    var cerrarPopover= event.target.closest("[data-interconsulta-lab-popover-close]");
    if (cerrarPopover) {
        event.preventDefault();
        cerrarNodoMiniHiloLaboratorioInterConsulta(true);
        return;
    }
    var media= event.target.closest("[data-interconsulta-lab-media]");
    if (media) {
        event.preventDefault();
        event.stopPropagation();
        var titulo= media.getAttribute("data-interconsulta-lab-media-name") || "Evidencia del trabajo";
        var abrir= function (archivo) {
            media._interconsultaLabMedia= archivo;
            mostrarVisorMiniHiloLaboratorioInterConsulta(archivo, titulo);
        };
        if (media._interconsultaLabMedia) {
            abrir(media._interconsultaLabMedia);
        } else if (window.TrabajoLaboratorio
            && typeof window.TrabajoLaboratorio.obtenerMedia === "function") {
            window.TrabajoLaboratorio.obtenerMedia(
                media.getAttribute("data-interconsulta-lab-media")
            ).then(abrir).catch(function (error) {
                if (typeof ver_vetana_informativa === "function") {
                    ver_vetana_informativa(error.message || "No se pudo abrir la evidencia.");
                }
            });
        }
        return;
    }
    var nodo= event.target.closest("[data-interconsulta-lab-node]");
    if (nodo) {
        event.preventDefault();
        event.stopPropagation();
        abrirNodoMiniHiloLaboratorioInterConsulta(nodo);
        return;
    }
    var mas= event.target.closest("[data-interconsulta-lab-more]");
    if (mas) {
        event.preventDefault();
        event.stopPropagation();
        cerrarNodoMiniHiloLaboratorioInterConsulta(false);
        var miniHilo= mas.closest(".interconsulta-lab-mini-thread");
        var expandido= !miniHilo.classList.contains("is-expanded");
        miniHilo.classList.toggle("is-expanded", expandido);
        mas.setAttribute("aria-expanded", expandido ? "true" : "false");
        mas.title= expandido ? "Ocultar nodos intermedios" : "Mostrar nodos intermedios";
        return;
    }
    var abrirTrabajo= event.target.closest("[data-interconsulta-lab-open-work]");
    if (abrirTrabajo) {
        event.preventDefault();
        event.stopPropagation();
        cerrarNodoMiniHiloLaboratorioInterConsulta(false);
        var idTrabajo= parseInt(
            abrirTrabajo.getAttribute("data-interconsulta-lab-open-work"),
            10
        ) || 0;
        if (window.TrabajoLaboratorio && typeof window.TrabajoLaboratorio.abrirTrabajo === "function") {
            window.TrabajoLaboratorio.abrirTrabajo(idTrabajo);
        } else if (typeof ver_vetana_informativa === "function") {
            ver_vetana_informativa("El módulo de trabajos de laboratorio no está disponible.");
        }
        return;
    }
    if (miniHiloLaboratorioPopoverInterConsulta
        && !miniHiloLaboratorioPopoverInterConsulta.hidden
        && !miniHiloLaboratorioPopoverInterConsulta.contains(event.target)) {
        cerrarNodoMiniHiloLaboratorioInterConsulta(false);
    }
});

document.addEventListener("keydown", function (event) {
    if (event.key !== "Escape") {
        return;
    }
    if (miniHiloLaboratorioViewerInterConsulta) {
        cerrarVisorMiniHiloLaboratorioInterConsulta();
        return;
    }
    cerrarNodoMiniHiloLaboratorioInterConsulta(true);
});

document.addEventListener("scroll", function (event) {
    if (miniHiloLaboratorioPopoverInterConsulta
        && miniHiloLaboratorioPopoverInterConsulta.contains(event.target)) {
        return;
    }
    cerrarNodoMiniHiloLaboratorioInterConsulta(false);
}, true);

window.addEventListener("resize", function () {
    cerrarNodoMiniHiloLaboratorioInterConsulta(false);
});

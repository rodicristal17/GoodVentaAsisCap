var agendaConsultoriosData = {
    consultorios: [],
    eventos: []
};
var timeoutBuscarHistorialPacienteCalendario = null;

function cargarAgendaConsultoriosDesdePHP() {
    obtener_datos_user();

    var paciente = document.getElementById('inptBuscarPacienteAgenda').value || '';
    var consultorio = document.getElementById('inptConsultorioAgendaFiltro').value || '';
    var local = document.getElementById('inptLocalAgendaFiltro').value || '';
    var estado = document.getElementById('inptEstadoAgenda').value || '';
    var fecha = document.getElementById('inptFechaAgenda').value || '';

    var datos = {
        "useru": userid,
        "passu": passuser,
        "navegador": navegador,
        "paciente": paciente,
        "cod_consultorio": consultorio,
        "cod_local": local,
        "fecha": fecha,
        "estado": estado,
        "ver_todos_consoltorios": controlacceso("VERTODOSLOSCONSULTORIOS", "accion"),
        "funt": "cargarAgenda"
    };

	verCerrarEfectoCargando("1");
    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmCalendar.php",
        type: "post",
        xhr: function () {
            var xhr = new window.XMLHttpRequest();

            xhr.upload.addEventListener("progress", function (evt) {
                var kb = ((evt.loaded * 1) / 1000).toFixed(1);
                if (kb == "0.0") {
                    kb = 0.1;
                }
                cargarConectividad("enviado", kb, "0");
            }, false);

            xhr.addEventListener("progress", function (evt) {
                var kb = ((evt.loaded * 1) / 1000).toFixed(1);
                if (kb == "0.0") {
                    kb = 0.1;
                }
                cargarConectividad("recibido", "0", kb);
            }, false);

            return xhr;
        },
        error: function (jqXHR, textstatus, errorThrowm) {
            console.error(jqXHR, textstatus, errorThrowm);
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
            document.getElementById("agendaGridConsultorios").innerHTML = '';
	        verCerrarEfectoCargando();
        },
        success: function (responseText) {
	        verCerrarEfectoCargando();
            try {
                var datosRespuesta = responseText;
                if (typeof responseText === "string") {
                    datosRespuesta = $.parseJSON(responseText);
                }

                var Respuesta = datosRespuesta["1"];
                Respuesta = respuestaJqueryAjax(Respuesta);

                if (Respuesta == true) {
                    agendaConsultoriosData.consultorios = datosRespuesta["consultorios"] || [];
                    agendaConsultoriosData.eventos = datosRespuesta["eventos"] || [];
					 
                    renderListaConsultoriosAgenda();
                    cargarSelectConsultoriosAgenda();
                    cargarAgendaConsultorios();
                } else {
                    document.getElementById("agendaGridConsultorios").innerHTML = "";
                }
            } catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR");
                var consolaTexto = "";

                try {
                    consolaTexto = (typeof responseText === "string")
                        ? responseText
                        : JSON.stringify(responseText);
                } catch(e) {
                    consolaTexto = "" + responseText;
                }

                var titulo = "Error: " + error + " \r\n Consola: " + consolaTexto;
                GuardarArchivosLog(titulo);
                console.log(responseText);
            }
        }
    });
}

var agendaDragState = {
    eventoId: null
};

var agendaResizeState = {
    activo: false,
    eventoId: null,
    startY: 0,
    finOriginal: '',
    inicioOriginal: '',
    consultorioOriginal: '',
    fechaOriginal: '',
    elemento: null,
    clickBloqueado: false
};

function iniciarAgendaConsultorios(){
    var hoy = new Date();
    var y = hoy.getFullYear();
    var m = ('0' + (hoy.getMonth() + 1)).slice(-2);
    var d = ('0' + hoy.getDate()).slice(-2);

    document.getElementById('inptFechaAgenda').value = y + '-' + m + '-' + d;
    document.getElementById('inptFechaNuevaCita').value = y + '-' + m + '-' + d;

    cargarAgendaConsultoriosDesdePHP();
}

function renderListaConsultoriosAgenda(){
    var cont = document.getElementById('listaConsultoriosAgenda');
    var html = '';
    var i, c;
    var idsSeleccionados = obtenerConsultoriosSeleccionadosAgenda();

    for(i = 0; i < agendaConsultoriosData.consultorios.length; i++){
        c = agendaConsultoriosData.consultorios[i];

        var checked = "checked";
        if(idsSeleccionados.length > 0){
            checked = idsSeleccionados.indexOf(String(c.id)) >= 0 ? "checked" : "";
        }

        html += ''
        + "<label class='item-consultorio item-consultorio-check'>"
            + "<input type='checkbox' "
                + "class='check-consultorio-agenda' "
                + "value='" + c.id + "' "
                + checked + " "
                + "onchange='cargarAgendaConsultorios()'>"
            + "<span class='consultorio-color' style='background:" + c.color + "'></span>"
            + "<div>"
                + "<b>" + c.nombre + "</b><br>"
                + "<span style='color:#6b7c90;font-size:11px'>" + c.descripcion + "</span>"
            + "</div>"
        + "</label>";
    }

    cont.innerHTML = html;
}


function obtenerConsultoriosSeleccionadosAgenda(){
    var checks = document.querySelectorAll('.check-consultorio-agenda:checked');
    var lista = [];
    var i;

    for(i = 0; i < checks.length; i++){
        lista.push(String(checks[i].value));
    }

    return lista;
}



function cargarSelectConsultoriosAgenda(){
    var html = "<option value=''>Seleccionar</option>";
    var htmlFiltro = "<option value=''>Todos</option>";
    var i, c;

    for(i = 0; i < agendaConsultoriosData.consultorios.length; i++){
        c = agendaConsultoriosData.consultorios[i];
        html += "<option value='" + c.id + "'>" + c.nombre + "</option>";
        htmlFiltro += "<option value='" + c.id + "'>" + c.nombre + "</option>";
    }

    document.getElementById('inptConsultorioAgenda').innerHTML = html;
    document.getElementById('inptConsultorioAgendaFiltro').innerHTML = htmlFiltro;
}
var intervaloLineaHoraActualAgenda = null;
 
function cargarAgendaConsultorios(){
    var fecha = document.getElementById('inptFechaAgenda').value;
    var estado = document.getElementById('inptEstadoAgenda').value;
    var consultoriosSeleccionados = obtenerConsultoriosSeleccionadosAgenda();

    var consultorios = [];
    var i, j, hora, minuto, contador_cita;
    var html = '';
    var textoHora = '';
    var datosAlmuerzo = null;
    var dataHoraRow = '';
    var htmlCuartosHora = '';

    for(i = 0; i < agendaConsultoriosData.consultorios.length; i++){
    if(
        consultoriosSeleccionados.length === 0 ||
        consultoriosSeleccionados.indexOf(String(agendaConsultoriosData.consultorios[i].id)) >= 0
    ){
        consultorios.push(agendaConsultoriosData.consultorios[i]);
    }
}

    html += "<div class='agenda-grid' id='agendaGridInterno' style='--total-consultorios:" + consultorios.length + "'>";

    html += "<div class='agenda-header-row' style='--total-consultorios:" + consultorios.length + "'>";
    html += "<div class='agenda-celda-hora-header'>Hora</div>";

    for(i = 0; i < consultorios.length; i++){
        contador_cita = calcularTotalesResumenAgenda(fecha, '', String(consultorios[i].id));
            
        html += "<div class='agenda-celda-consultorio' onclick='vercerrarModalAbmConsultorioAgenda(true);obtenerDatosAbmConsultorioAgenda(this)'>"
            + "<span id='td_id' style='display:none;'>"+consultorios[i].id+"</span>"
            + "<span id='td_datos_2' style='text-decoration: underline; color: blue;'>"+consultorios[i].nombre+"</span>"
            + "<span id='td_datos_4' style='display:none;'>"+consultorios[i].cod_doctorFK+"</span>"
            + "<span id='td_datos_3'>"+consultorios[i].nombre_doctor+"</span>"
            + "<span class='agenda-consultorio-sub'>" + consultorios[i].descripcion + "</span>"

            + "<span id='td_datos_5' class='agenda-consultorio-sub' style='display:none;'>" + contador_cita.total + "</span>"
            + "<span id='td_datos_6' class='agenda-consultorio-sub' style='display:none;'>" + contador_cita.confirmadas + "</span>"
            + "<span id='td_datos_7' class='agenda-consultorio-sub' style='display:none;'>" + contador_cita.pendientes + "</span>"
            + "<span id='td_datos_8' class='agenda-consultorio-sub' style='display:none;'>" + contador_cita.canceladas + "</span>"
            + "<span id='td_datos_9' class='agenda-consultorio-sub' style='display:none;'>" + contador_cita.PrimeraConsulta + "</span>"
            + "<span id='td_datos_10' class='agenda-consultorio-sub' style='display:none;'>" + contador_cita.Atendido + "</span>"
            + "<span id='td_datos_12' class='agenda-consultorio-sub' style='display:none;'>" + contador_cita.ConDeuda + "</span>"
            + "</div>";
    }

    html += "</div>";

    for(hora = 7; hora <= 22; hora++){
        for(minuto = 0; minuto <= 30; minuto += 30){
            if(hora === 22 && minuto === 30){
                continue;
            }

            textoHora = completarHora(hora) + ":" + completarHora(minuto);
            dataHoraRow = hora + "-" + minuto;
            datosAlmuerzo = obtenerDatosAlmuerzoAgendaMediaHora(fecha, hora, minuto);

            html += "<div class='agenda-row agenda-row-mediahora" + (hora >= 18 ? " agenda-row-horario-tarde" : "") + "' style='--total-consultorios:" + consultorios.length + "' data-hora-row='" + dataHoraRow + "'>";
            html += "<div class='agenda-hora" + datosAlmuerzo.claseHora + "'>" + textoHora + "</div>";

            for(j = 0; j < consultorios.length; j++){
                html += "<div class='agenda-slot agenda-slot-mediahora" + datosAlmuerzo.claseSlot + "' "
				+ "data-consultorio='" + consultorios[j].id + "' "
				+ "data-fecha='" + fecha + "' "
				+ "data-hora='" + textoHora + "' "
				+ "onclick='clickSlotAgenda(this, event)'>"
				+ datosAlmuerzo.htmlOverlay
				+ "</div>";
            }

            html += "</div>";
        }
    }

    html += "<div class='linea-hora-actual' id='lineaHoraActualAgenda' style='display:none;'><span class='etiqueta-hora-actual' id='etiquetaHoraActualAgenda'></span></div>";
    html += "</div>";

    document.getElementById('agendaGridConsultorios').innerHTML = html;

    pintarEventosAgenda(fecha, estado, consultoriosSeleccionados);
	actualizarResumenAgenda(fecha, estado, consultoriosSeleccionados);
    actualizarResumenFiltrosAgenda();
    inicializarDragAndDropAgenda();
    inicializarBarraHorizontalAgenda();
    iniciarLineaHoraActualAgenda();
}

function inicializarBarraHorizontalAgenda(){
    var wrapper = document.getElementById('agendaGridConsultorios');
    var barra = document.getElementById('agendaScrollHorizontalAgenda');
    var contenedorBarra;
    var marcoAgenda;

    if(!wrapper){
        return;
    }

    if(!barra){
        contenedorBarra = document.createElement('div');
        contenedorBarra.className = 'agenda-scroll-horizontal';
        contenedorBarra.id = 'agendaScrollHorizontalAgendaCont';
        contenedorBarra.innerHTML = "<input type='range' id='agendaScrollHorizontalAgenda' min='0' max='0' value='0' step='1' aria-label='Desplazamiento horizontal de agenda'>";

        marcoAgenda = wrapper.parentNode;
        if(!marcoAgenda || !marcoAgenda.parentNode){
            return;
        }

        marcoAgenda.parentNode.insertBefore(contenedorBarra, marcoAgenda.nextSibling);
        barra = document.getElementById('agendaScrollHorizontalAgenda');
    }else{
        contenedorBarra = document.getElementById('agendaScrollHorizontalAgendaCont');
    }

    function actualizarBarra(){
        var maxScroll = Math.max(0, wrapper.scrollWidth - wrapper.clientWidth);

        barra.max = maxScroll;
        barra.value = Math.min(wrapper.scrollLeft, maxScroll);

        if(contenedorBarra){
            contenedorBarra.style.display = maxScroll > 0 ? 'block' : 'none';
        }
    }

    if(!barra._agendaInputInicializado){
        barra.addEventListener('input', function(){
            wrapper.scrollLeft = parseInt(this.value, 10) || 0;
        }, false);
        barra._agendaInputInicializado = true;
    }

    if(!wrapper._agendaScrollBarraInicializada){
        wrapper.addEventListener('scroll', function(){
            var barraActual = document.getElementById('agendaScrollHorizontalAgenda');
            if(barraActual){
                barraActual.value = wrapper.scrollLeft;
            }
        }, false);

        if(window.addEventListener){
            window.addEventListener('resize', function(){
                inicializarBarraHorizontalAgenda();
            }, false);
        }

        wrapper._agendaScrollBarraInicializada = true;
    }

    setTimeout(actualizarBarra, 0);
}

function calcularHoraSlotQuinceMinutos(slot, ev){
    var horaBase = slot.getAttribute('data-hora') || '';
    var minBase = horaAMinutos(horaBase);
    var rect, offsetY;

    if(!horaBase || isNaN(minBase)){
        return horaBase;
    }

    if(ev && typeof ev.clientY === 'number' && slot.getBoundingClientRect){
        rect = slot.getBoundingClientRect();
        offsetY = ev.clientY - rect.top;

        if(offsetY >= (rect.height / 2)){
            minBase += 15;
        }
    }

    return minutosAHora(minBase);
}


function clickSlotAgenda(slot, ev){
    if(!slot){
        return;
    }

    if(ev && ev.target && closestByClass(ev.target, 'agenda-evento')){
        return;
    }

    if(agendaResizeState.activo){
        return;
    }

    var consultorio = slot.getAttribute('data-consultorio') || '';
    var fecha = slot.getAttribute('data-fecha') || '';
    var hora = slot.getAttribute('data-hora') || '';
    var horaFin = '';

    hora = calcularHoraSlotQuinceMinutos(slot, ev);
    horaFin = minutosAHora(horaAMinutos(hora) + 15);

    vercerrarModalNuevaCita(true);

    document.getElementById('inptConsultorioAgenda').value = consultorio;
    document.getElementById('inptFechaNuevaCita').value = fecha;
    document.getElementById('inptHoraInicioAgenda').value = hora;
    document.getElementById('inptHoraFinAgenda').value = horaFin;

    if(document.getElementById('inptEstadoNuevaCita')){
        document.getElementById('inptEstadoNuevaCita').value = 'AGENDADO';
    }
}

function closestByClass(elemento, clase){
    while(elemento){
        if(elemento.classList && elemento.classList.contains(clase)){
            return elemento;
        }
        elemento = elemento.parentNode;
    }
    return null;
}

function pintarEventosAgenda(fecha, estado, consultoriosSeleccionados){
    var slots = document.querySelectorAll('.agenda-slot');
    var i, j, slot, consultorioId, horaSlot, eventosConsultorio, htmlEventos;
    var partesHora, horaNumero, minutoNumero, datosAlmuerzo, htmlFinal, htmlCuartosHora;

    for(i = 0; i < slots.length; i++){
        slot = slots[i];
        consultorioId = slot.getAttribute('data-consultorio');
        horaSlot = slot.getAttribute('data-hora');

        partesHora = horaSlot.split(':');
        horaNumero = parseInt(partesHora[0], 10);
        minutoNumero = parseInt(partesHora[1], 10);

        eventosConsultorio = obtenerEventosFiltradosConsultorio(fecha, estado, consultoriosSeleccionados, consultorioId);
        htmlEventos = '';

        for(j = 0; j < eventosConsultorio.length; j++){
            if(obtenerHoraVisualAgenda(eventosConsultorio[j].inicio) === horaSlot){
                htmlEventos += renderEventoAgenda(eventosConsultorio[j], eventosConsultorio);
            }
        }

        datosAlmuerzo = obtenerDatosAlmuerzoAgendaMediaHora(fecha, horaNumero, minutoNumero);
        htmlFinal = datosAlmuerzo.htmlOverlay + htmlEventos;
        slot.innerHTML = htmlFinal;
    }
}
function obtenerHoraVisualAgenda(h){
    if(!h){
        return '';
    }

    var partes = h.split(':');
    var hora = parseInt(partes[0], 10);
    var minuto = parseInt(partes[1], 10);

    if(isNaN(hora)){ hora = 0; }
    if(isNaN(minuto)){ minuto = 0; }

    // redondea hacia abajo al bloque visual de 30 minutos
    if(minuto >= 30){
        minuto = 30;
    }else{
        minuto = 0;
    }

    return completarHora(hora) + ":" + completarHora(minuto);
}

function calcularTopEvento(inicio){
    var minutosInicio = horaAMinutos(inicio);
    var horaVisual = obtenerHoraVisualAgenda(inicio);
    var minutosBase = horaAMinutos(horaVisual);

    // diferencia dentro del bloque de 30 min
    var offsetMinutos = minutosInicio - minutosBase;

    // cada 30 min equivalen a 45px en tu grilla
    return Math.round((offsetMinutos / 30) * 45);
}

function calcularAlturaEvento(inicio, fin){
    var min1 = horaAMinutos(inicio);
    var min2 = horaAMinutos(fin);
    var diferencia = min2 - min1;

    // misma escala que la grilla: 30 min = 45px
    return Math.max(18, Math.round((diferencia / 30) * 45));
}

function iniciarLineaHoraActualAgenda(){
    if(intervaloLineaHoraActualAgenda){
        clearInterval(intervaloLineaHoraActualAgenda);
        intervaloLineaHoraActualAgenda = null;
    }

    actualizarLineaHoraActualAgenda();
    // scrollAHoraActualAgenda();

    intervaloLineaHoraActualAgenda = setInterval(function(){
        actualizarLineaHoraActualAgenda();
    }, 10000);
}

function scrollAHoraActualAgenda(){
    var inputFecha = document.getElementById('inptFechaAgenda');
    var wrapper = document.getElementById('agendaGridConsultorios');
    var ahora = new Date();
    var yyyy = ahora.getFullYear();
    var mm = ('0' + (ahora.getMonth() + 1)).slice(-2);
    var dd = ('0' + ahora.getDate()).slice(-2);
    var fechaHoy = yyyy + "-" + mm + "-" + dd;
    var minutoBase;
    var selector;
    var filaHora;

    if(!inputFecha || !wrapper || inputFecha.value !== fechaHoy){
        return;
    }

    minutoBase = ahora.getMinutes() >= 30 ? 30 : 0;
    selector = ".agenda-row[data-hora-row='" + ahora.getHours() + "-" + minutoBase + "']";
    filaHora = document.querySelector(selector);

    if(filaHora){
        wrapper.scrollTop = filaHora.offsetTop - 120;
    }
}
function actualizarLineaHoraActualAgenda(){
    var inputFecha = document.getElementById('inptFechaAgenda');
    var linea = document.getElementById('lineaHoraActualAgenda');
    var etiqueta = document.getElementById('etiquetaHoraActualAgenda');
    var gridInterno = document.getElementById('agendaGridInterno');
    var ahora = new Date();
    var yyyy, mm, dd, fechaHoy, fechaSeleccionada;
    var horaActual, minutoActual, minutoBase, selector, filaHora;
    var topFila, altoFila, offsetMinutos, topFinal;

    if(!inputFecha || !linea || !etiqueta || !gridInterno){
        return;
    }

    fechaSeleccionada = inputFecha.value;
    yyyy = ahora.getFullYear();
    mm = ('0' + (ahora.getMonth() + 1)).slice(-2);
    dd = ('0' + ahora.getDate()).slice(-2);
    fechaHoy = yyyy + "-" + mm + "-" + dd;

    if(fechaSeleccionada !== fechaHoy){
        linea.style.display = 'none';
        return;
    }

    horaActual = ahora.getHours();
    minutoActual = ahora.getMinutes();

    if(horaActual < 7 || horaActual > 22 || (horaActual === 22 && minutoActual > 0)){
        linea.style.display = 'none';
        return;
    }

    minutoBase = minutoActual >= 30 ? 30 : 0;
    selector = ".agenda-row[data-hora-row='" + horaActual + "-" + minutoBase + "']";
    filaHora = document.querySelector(selector);

    if(!filaHora){
        linea.style.display = 'none';
        return;
    }

    topFila = filaHora.offsetTop;
    altoFila = filaHora.offsetHeight;

    if(minutoBase === 0){
        offsetMinutos = (minutoActual / 30) * altoFila;
    }else{
        offsetMinutos = ((minutoActual - 30) / 30) * altoFila;
    }

    topFinal = topFila + offsetMinutos;

    linea.style.top = topFinal + "px";
    linea.style.display = 'block';
    etiqueta.innerHTML = completarHora(horaActual) + ":" + completarHora(minutoActual);
}

function obtenerDatosAlmuerzoAgendaMediaHora(fecha, hora, minuto){
    var fechaObj = null;
    var esSabado = false;
    var inicioBloque = (hora * 60) + minuto;
    var finBloque = inicioBloque + 30;

    var inicioAlmuerzo = 12 * 60;
    var finAlmuerzo = 14 * 60;

    var datos = {
        esAlmuerzo: false,
        claseHora: '',
        claseSlot: '',
        htmlOverlay: ''
    };

    if(fecha){
        fechaObj = new Date(fecha + 'T00:00:00');
        esSabado = (fechaObj.getDay() === 6);
    }

    if(esSabado){
        inicioAlmuerzo = (11 * 60) + 30; /* 11:30 */
        finAlmuerzo = 13 * 60;           /* 13:00 */
    }

    if(inicioBloque < finAlmuerzo && finBloque > inicioAlmuerzo){
        datos.esAlmuerzo = true;
        datos.claseSlot = ' agenda-slot-almuerzo';

        if((hora === 12 && minuto === 0) || (!esSabado && hora === 13 && minuto === 0)){
            datos.claseHora = ' agenda-hora-almuerzo';
        }

        if(esSabado){
            datos.htmlOverlay = "<div class='bloque-almuerzo-slot' style='top:0;height:100%;'></div>"
                + "<div class='texto-almuerzo-slot'>Almuerzo 11:30 - 13:00</div>";
        }else{
            datos.htmlOverlay = "<div class='bloque-almuerzo-slot' style='top:0;height:100%;'></div>"
                + "<div class='texto-almuerzo-slot'>Hora de almuerzo</div>";
        }
    }

    return datos;
}

 

function obtenerEventosFiltradosConsultorio(fecha, estado, consultoriosSeleccionados, consultorioId){
    var lista = [];
    var i, ev;
    var filtrarPorChecks = consultoriosSeleccionados && consultoriosSeleccionados.length > 0;

    for(i = 0; i < agendaConsultoriosData.eventos.length; i++){
        ev = agendaConsultoriosData.eventos[i];
        if(
            ev.fecha === fecha &&
            String(ev.consultorio) === String(consultorioId) &&
            (estado === '' || ev.estado === estado) &&
            (!filtrarPorChecks || consultoriosSeleccionados.indexOf(String(ev.consultorio)) >= 0)
        ){
            lista.push(ev);
        }
    }

    lista.sort(function(a, b){
        var diff = 0;

        diff = String(a.fecha).localeCompare(String(b.fecha));
        if(diff !== 0){
            return diff;
        }

        diff = parseInt(a.consultorio, 10) - parseInt(b.consultorio, 10);
        if(diff !== 0){
            return diff;
        }

        diff = horaAMinutos(a.inicio) - horaAMinutos(b.inicio);
        if(diff !== 0){
            return diff;
        }

        diff = horaAMinutos(a.fin) - horaAMinutos(b.fin);
        if(diff !== 0){
            return diff;
        }

        return parseInt(a.id, 10) - parseInt(b.id, 10);
    });

    return lista;
}
function renderEventoAgenda(e, eventosMismoConsultorio){
    var altura = calcularAlturaEvento(e.inicio, e.fin);
    var top = calcularTopEvento(e.inicio);
    var pos = calcularPosicionLateral(e, eventosMismoConsultorio);

    var total = pos.total;
    var indice = pos.indice;

    var width = total > 1 ? "calc(" + (100 / total) + "% - 6px)" : "calc(100% - 16px)";
    var left = total > 1 ? "calc(" + indice + " * (100% / " + total + ") + 4px)" : "8px";

    var MiColor = "#0d6efd";


    if(e.estado == "AGENDADO"){
        MiColor = "#80c583";
    }	
	else if(e.estado == "CONFIRMADO"){
        MiColor = "#3b833e";
    } 	
	else if(e.estado == "ATENDIDO"){
        MiColor = "#833b3b";
    }	
	else if(e.estado == "CANCELADO"){
        MiColor = "#6c757d";
	}	
	else if(e.estado == "ENESPERA"){
        MiColor = "#dcb645";
    }
	else if(e.estado == "CONFIRMADOCONDEUDA"){
        MiColor = "#07488f";
    }	
	else if(e.estado == "PRIMERACONSULTA"){
        MiColor = "#9d457c";
    }
	 
    // Evalua si tiene los datos basicos
    let advertencia_datos_incompletos= '';
    if (!e.idzonaFk || e.idzonaFk == 0 || !e.whapp || !e.ci_cliente) {
        advertencia_datos_incompletos= '<i class="fa-solid fa-triangle-exclamation" style="color: gold;padding-right: 5px;"></i>';
    }

    var estilos = ""
        + "background:" + MiColor + ";"
        + "top:" + top + "px;"
        + "left:" + left + ";"
        + "width:" + width + ";"
        + "height:" + altura + "px;"
        + "right:auto;"
        + "overflow:visible;"
		+ "border:1px solid #ccc;";

    return ''
    + "<div class='agenda-evento estado-" + e.estado + "' "
    + "draggable='true' "
    + "data-id='" + e.id + "' "
    + "data-consultorio='" + e.consultorio + "' "
    + "data-fecha='" + e.fecha + "' "
    + "data-inicio='" + e.inicio + "' "
    + "data-fin='" + e.fin + "' "
    + "style='" + estilos + "' "
    + "onclick='clickEventoAgenda(" + e.id + ", event)'>"
    + "<span class='paciente'>" + advertencia_datos_incompletos + e.paciente + "</span>"
    + "<span class='nombre_doctor'>" + (e.nombre_doctor || '') + "</span>"
    + "<span class='ci_cliente' style='display: none;'>" + e.ci_cliente + "</span>"
        + "<span class='hora'>" + e.inicio + " - " + e.fin + "</span>"
        + "<span class='detalle' style='display:none;'>" + (e.motivo || '') + "</span>"
/*        + "<div class='agenda-evento-resize' "
            + "data-id='" + e.id + "' "
            + "title='Arrastrar para alargar o acortar horario'></div>"*/
    + "</div>";
}
function clickEventoAgenda(id, ev){
    if(ev && ev.defaultPrevented){
        return;
    }

    if(agendaResizeState.clickBloqueado){
        return;
    }

    verDetalleAgenda(id);
}

function eventosSeSolapan(e1, e2){
    var ini1 = horaAMinutos(e1.inicio);
    var fin1 = horaAMinutos(e1.fin);
    var ini2 = horaAMinutos(e2.inicio);
    var fin2 = horaAMinutos(e2.fin);

    return ini1 < fin2 && fin1 > ini2;
}

function calcularPosicionLateral(evento, listaEventosMismoConsultorio){
    var conflictos = [];
    var i;

    for(i = 0; i < listaEventosMismoConsultorio.length; i++){
        if(eventosSeSolapan(evento, listaEventosMismoConsultorio[i])){
            conflictos.push(listaEventosMismoConsultorio[i]);
        }
    }

    conflictos.sort(function(a, b){
        var diff = horaAMinutos(a.inicio) - horaAMinutos(b.inicio);
        if(diff !== 0){
            return diff;
        }
        return horaAMinutos(a.fin) - horaAMinutos(b.fin);
    });

    var indice = 0;
    for(i = 0; i < conflictos.length; i++){
        if(String(conflictos[i].id) === String(evento.id)){
            indice = i;
            break;
        }
    }

    return {
        total: conflictos.length,
        indice: indice
    };
}

  
function horaAMinutos(h){
    var p = h.split(':');
    return (parseInt(p[0], 10) * 60) + parseInt(p[1], 10);
}

function minutosAHora(minutos){
    var h = Math.floor(minutos / 60);
    var m = minutos % 60;
    return completarHora(h) + ":" + completarHora(m);
}

function obtenerHoraNumero(h){
    return parseInt(h.split(':')[0], 10);
}

function obtenerHoraTexto(h){
    var partes = h.split(':');
    return completarHora(parseInt(partes[0], 10)) + ":" + completarHora(parseInt(partes[1], 10));
}

function completarHora(n){
    return (n < 10 ? '0' : '') + n;
}

function esDomingoFechaAgenda(fecha){
    var partes, anio, mes, dia, fechaUtc;

    if(!fecha){
        return false;
    }

    partes = fecha.split('-');
    if(partes.length !== 3){
        return false;
    }

    anio = parseInt(partes[0], 10);
    mes = parseInt(partes[1], 10);
    dia = parseInt(partes[2], 10);

    if(isNaN(anio) || isNaN(mes) || isNaN(dia)){
        return false;
    }

    fechaUtc = new Date(Date.UTC(anio, mes - 1, dia));
    return fechaUtc.getUTCDay() === 0;
}

function calcularTotalesResumenAgenda(fecha, estado, consultorioFiltro){
    var totales = {
        total: 0,
        confirmadas: 0,
        pendientes: 0,
        canceladas: 0,
        PrimeraConsulta: 0,
        Atendido: 0,
        ConDeuda: 0
    };
    var i, e, filtrarPorConsultorios;

    filtrarPorConsultorios = Array.isArray(consultorioFiltro)
        ? consultorioFiltro.length > 0
        : consultorioFiltro !== '';

    for(i = 0; i < agendaConsultoriosData.eventos.length; i++){
        e = agendaConsultoriosData.eventos[i];
        if(
            e.fecha === fecha &&
            (estado === '' || e.estado === estado) &&
            (
                !filtrarPorConsultorios ||
                (
                    Array.isArray(consultorioFiltro)
                        ? consultorioFiltro.indexOf(String(e.consultorio)) >= 0
                        : String(e.consultorio) === String(consultorioFiltro)
                )
            )
        ){
            totales.total++;
            if(e.estado === 'CONFIRMADO'){ totales.confirmadas++; }
            if(e.estado === 'AGENDADO'){ totales.pendientes++; }
            if(e.estado === 'CANCELADO'){ totales.canceladas++; }
            if(e.estado === 'PRIMERACONSULTA'){ totales.PrimeraConsulta++; }
            if(e.estado === 'ATENDIDO'){ totales.Atendido++; }
            if(e.estado === 'CONFIRMADOCONDEUDA'){ totales.ConDeuda++; }
        }
    }

    return totales;
}

function actualizarResumenAgenda(fecha, estado, consultorioFiltro){
    var totales = calcularTotalesResumenAgenda(fecha, estado, consultorioFiltro);

    document.getElementById('lblTotalCitasAgenda').innerHTML = totales.total;
    document.getElementById('lblConfirmadasAgenda').innerHTML = totales.confirmadas;
    document.getElementById('lblPendientesAgenda').innerHTML = totales.pendientes;
    document.getElementById('lblCanceladasAgenda').innerHTML = totales.canceladas;
    document.getElementById('lblPrimeraConsultaAgenda').innerHTML = totales.PrimeraConsulta;
    document.getElementById('lblAtendidoAgenda').innerHTML = totales.Atendido;
    document.getElementById('lblConDeudaAgenda').innerHTML = totales.ConDeuda;
}

function actualizarResumenFiltrosAgenda(){
    var fecha = document.getElementById('inptFechaAgenda').value || 'hoy';
    var consultorio = document.getElementById('inptConsultorioAgendaFiltro');
    var textoConsultorio = 'todos';

    if(consultorio && consultorio.selectedIndex >= 0){
        textoConsultorio = consultorio.options[consultorio.selectedIndex].text;
    }

    document.getElementById('agendaFiltrosActivos').innerHTML = ''
        + "<span class='chip-filtro'>Fecha: " + fecha + "</span>"
        + "<span class='chip-filtro'>Consultorios: " + textoConsultorio + "</span>";
}

function cambiarFechaAgenda(dias){
    var fechaInput = document.getElementById('inptFechaAgenda');
    if(fechaInput.value === ''){
        return;
    }

    var f = new Date(fechaInput.value + 'T00:00:00');
    f.setDate(f.getDate() + dias);
    fechaInput.value = formatearFechaInput(f);
    cargarAgendaConsultoriosDesdePHP();
}


function irHoyAgenda(){
    var hoy = new Date();
    document.getElementById('inptFechaAgenda').value = formatearFechaInput(hoy);
    cargarAgendaConsultoriosDesdePHP();
}

function formatearFechaInput(fecha){
    var y = fecha.getFullYear();
    var m = ('0' + (fecha.getMonth() + 1)).slice(-2);
    var d = ('0' + fecha.getDate()).slice(-2);
    return y + '-' + m + '-' + d;
}

function vercerrarModalFiltrosAgenda(mostrar){
    if (mostrar) {
        document.getElementById('overlayFiltrosAgenda').style.display = '';
        document.getElementById('modalFiltrosAgenda').style.display = '';
    } else {
        document.getElementById('overlayFiltrosAgenda').style.display = 'none';
        document.getElementById('modalFiltrosAgenda').style.display = 'none';
    }
}

function aplicarFiltrosAgenda(){
    vercerrarModalFiltrosAgenda(false);
    cargarAgendaConsultoriosDesdePHP();
}

function limpiarFiltrosAgenda(){
    document.getElementById('inptBuscarPacienteAgenda').value = '';
    document.getElementById('inptConsultorioAgendaFiltro').value = '';
    document.getElementById('inptLocalAgendaFiltro').value = '';
    document.getElementById('inptEstadoAgenda').value = '';  

    document.getElementById("seccion_historial_agenda").style.display = "none";
    document.getElementById("seccion_historial_agenda").parentElement.style.gridTemplateColumns= '1fr';
    document.getElementById("table_historial_paciente_agenda").innerHTML = "";
    
    cargarAgendaConsultoriosDesdePHP();
}

function vercerrarModalNuevaCita(mostrar){
    if (mostrar) {
        if(controlacceso("INSERTARFORMULARIOCALENDARIO","accion")==false){return;}
                 
        document.getElementById('inptFechaNuevaCita').value = document.getElementById('inptFechaAgenda').value;
        document.getElementById('overlayNuevaCita').style.display = '';
        document.getElementById('modalNuevaCita').style.display = '';
    } else {
        document.getElementById('overlayNuevaCita').style.display = 'none';
        document.getElementById('modalNuevaCita').style.display = 'none';
    }
}

function vercerrarModalAbmConsultorioAgenda(mostrar){
    if (mostrar) {
        //if(controlacceso("INSERTARFORMULARIOCALENDARIO","accion")==false){return;}
                 
        document.getElementById('overlayNuevaCita').style.display = '';
        document.getElementById('modalAbmConsultorioAgenda').style.display = '';
    } else {
        document.getElementById('overlayNuevaCita').style.display = 'none';
        document.getElementById('modalAbmConsultorioAgenda').style.display = 'none';
    }
}

function verificarAbmConsultorioAgenda() {
    const inptDoctorAbmConsultorioAgenda= document.getElementById('inptDoctorAbmConsultorioAgenda').value;
    const inptIDAbmConsultorioAgenda= document.getElementById('inptIDAbmConsultorioAgenda').value;
	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"doctor": inptDoctorAbmConsultorioAgenda,
        "cod_consultorio": inptIDAbmConsultorioAgenda,
		"funt": "actualizarDoctor"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmConsultorio.php",
		type: "post",
		xhr: function () {
			var xhr = new window.XMLHttpRequest();
			//Uload progress
			xhr.upload.addEventListener("progress" ,function (evt) {
			var kb=((evt.loaded*1)/1000).toFixed(1)
			if(kb=="0.0"){
			kb=0.1;
			}
			cargarConectividad("enviado",kb,"0")           
			}, false);
			//Download progress
			xhr.addEventListener("progress", function (evt) {
			var kb=((evt.loaded*1)/1000).toFixed(1)
			if(kb=="0.0"){
			kb=0.1;
			}
			cargarConectividad("recibido","0",kb)  
			}, false);
			return xhr;
		},
		error: function (jqXHR, textstatus, errorThrowm) {
			manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
		},
		success: function (responseText) {
			var Respuesta = responseText;
			console.log(Respuesta)
			
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				 Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					ver_vetana_informativa("Datos Guardados con exito");
                    vercerrarModalAbmConsultorioAgenda(false);
                    cargarAgendaConsultoriosDesdePHP();
				}else{
    				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function obtenerMotivoCitaAgendaConCreador(nombreInputElement){
    var motivo = document.getElementById(nombreInputElement).value.trim();

    if(motivo === ''){
        return '';
    }

    return "@{" + userid + "}" + ": " + motivo;
}

function guardarCitaAgenda(){
    var paciente = document.getElementById('inptPacienteAgenda').value;
    var consultorio = document.getElementById('inptConsultorioAgenda').value;
    var fecha = document.getElementById('inptFechaNuevaCita').value;
    var inicio = document.getElementById('inptHoraInicioAgenda').value;
    var fin = document.getElementById('inptHoraFinAgenda').value;
    var estado = document.getElementById('inptEstadoNuevaCita').value;
    var motivo = obtenerMotivoCitaAgendaConCreador('inptMotivoAgenda');

    if(paciente === '' || consultorio === '' || fecha === '' || inicio === '' || fin === ''){
        alert('Complete los datos obligatorios de la cita.');
        return;
    }

    if(horaAMinutos(fin) <= horaAMinutos(inicio)){
        alert('La hora fin debe ser mayor a la hora inicio.');
        return;
    }

    vercerrarModalNuevaCita(false);
    
    agendaConsultoriosData.eventos.push({
        id: new Date().getTime(),
        consultorio: parseInt(consultorio, 10),
        paciente: paciente,
        fecha: fecha,
        inicio: inicio,
        fin: fin,
        estado: estado,
        motivo: motivo
    });

    limpiarFormularioNuevaCita();
    cargarAgendaConsultorios();
}

function limpiarFormularioNuevaCita(){
    document.getElementById('inptPacienteAgenda').value = '';
    document.getElementById('inptConsultorioAgenda').value = '';
    document.getElementById('inptHoraInicioAgenda').value = '';
    document.getElementById('inptHoraFinAgenda').value = '';
    document.getElementById('inptEstadoNuevaCita').value = 'AGENDADO';
    document.getElementById('inptMotivoAgenda').value = '';
    idAbmAgenda= "";
}

function obtenerComentariosAgendamiento() {
 	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"idAgenda": idAbmAgenda,
		"funt": "obtenerComentarios"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmAgenda.php",
		type: "post",
        dataType: "text",
		xhr: function () {
			var xhr = new window.XMLHttpRequest();
			//Uload progress
			xhr.upload.addEventListener("progress" ,function (evt) {
                var kb=((evt.loaded*1)/1000).toFixed(1)
                if(kb=="0.0"){
                    kb=0.1;
                }
                cargarConectividad("enviado",kb,"0")           
			}, false);
			//Download progress
			xhr.addEventListener("progress", function (evt) {
                var kb=((evt.loaded*1)/1000).toFixed(1)
                if(kb=="0.0"){
                    kb=0.1;
                }
                cargarConectividad("recibido","0",kb)  
			}, false);
			return xhr;
		},
		error: function (jqXHR, textstatus, errorThrowm) {
            console.error(jqXHR, textstatus, errorThrowm);
			manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
		},
		success: function (responseText) {
            console.log(responseText);
			try {
				var datos = typeof responseText === "string" ? $.parseJSON(responseText) : responseText;
				var Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
                    var contenedorComentarios = document.getElementById('detAgendaMotivo');
                    var motivoBase = contenedorComentarios.getAttribute('data-motivo-base') || '';
                    contenedorComentarios.innerHTML = motivoBase + (datos["3"] || '');
				}else{
    				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				}
			} catch (error) {
                console.error(error, responseText);
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function verificarComentarioAgendamiento() {
    const detAgendaMotivoInput= document.getElementById('detAgendaMotivoInput').value;
    if (!detAgendaMotivoInput) {
        ver_vetana_informativa("Ingrese un comentario para guardar");
        return false;
    }

 	obtener_datos_user();
	var datos = {
		"useru": userid,
		"passu": passuser,
		"navegador": navegador,
		"idAgenda": idAbmAgenda,
        "comentario": detAgendaMotivoInput,
		"funt": "crearComentario"
	};
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmAgenda.php",
		type: "post",
        dataType: "text",
		xhr: function () {
			var xhr = new window.XMLHttpRequest();
			//Uload progress
			xhr.upload.addEventListener("progress" ,function (evt) {
                var kb=((evt.loaded*1)/1000).toFixed(1)
                if(kb=="0.0"){
                    kb=0.1;
                }
                cargarConectividad("enviado",kb,"0")           
			}, false);
			//Download progress
			xhr.addEventListener("progress", function (evt) {
                var kb=((evt.loaded*1)/1000).toFixed(1)
                if(kb=="0.0"){
                    kb=0.1;
                }
                cargarConectividad("recibido","0",kb)  
			}, false);
			return xhr;
		},
		error: function (jqXHR, textstatus, errorThrowm) {
			manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana")
		},
		success: function (responseText) {
			try {
				var datos = typeof responseText === "string" ? $.parseJSON(responseText) : responseText;
				var Respuesta = datos["1"];
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta == true) {
					ver_vetana_informativa("Datos Guardados con exito");
                    document.getElementById('detAgendaMotivoInput').value = '';
                    obtenerComentariosAgendamiento();
				}else{
    				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				}
			} catch (error) {
                console.error(error);
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
				var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

var idAbmAgenda= "";
function verDetalleAgenda(id){
    var evento = null;
    var i;

    for(i = 0; i < agendaConsultoriosData.eventos.length; i++){
        if(String(agendaConsultoriosData.eventos[i].id) === String(id)){
            evento = agendaConsultoriosData.eventos[i];
            break;
        }
    }

    if(!evento){
        alert('No se encontró el agendamiento.');
        return;
    }

    var nombreConsultorio = '';
    for(i = 0; i < agendaConsultoriosData.consultorios.length; i++){
        if(String(agendaConsultoriosData.consultorios[i].id) === String(evento.consultorio)){
            nombreConsultorio = agendaConsultoriosData.consultorios[i].nombre;
            break;
        }
    }

    // Se evalua si los datos del cliente estan completos
    let advertencia_datos_cliente_incompleto= '';
    if (!evento.ci_cliente || !evento.whapp || !evento.idzonaFk || evento.idzonaFk == 0) {
        advertencia_datos_cliente_incompleto= '<i class="fa-solid fa-triangle-exclamation" style="color: gold;padding-left: 5px;"></i>';
        advertencia_datos_cliente_incompleto += '<br><input type="button" value="Cargar datos faltantes" class="btn4" onclick="controlseleccvistacliente= \'calendario\';verCerrarVentanaAbmCliente(true, true, true);cerrarDetalleAgenda();cerrarAgendaConsultorios()" style="width:fit-content;margin-top: 20px;padding: 6px 12px;background: #b40303;"/>';
    }

    idAbmAgenda = evento.id;
    document.getElementById('detAgendaId').innerHTML = evento.id;
    document.getElementById('detAgendaPaciente').innerHTML = evento.paciente + advertencia_datos_cliente_incompleto;
    document.getElementById('detAgendaCedula').innerHTML = evento.ci_cliente || '';
    document.getElementById('detAgendaPresupuesto').innerHTML = (evento.nombre_doctor ? (evento.nombre_doctor + '<br>') : '');
    document.getElementById('detAgendaFecha').innerHTML = evento.fecha || '';
    document.getElementById('detAgendaHorarioInicio').value = evento.inicio || '';
    document.getElementById('detAgendaHorarioFin').value = evento.fin || '';
    document.getElementById('detAgendaMotivo').innerHTML = evento.motivo_limpio || '';
    document.getElementById('detAgendaMotivo').setAttribute('data-motivo-base', evento.motivo_limpio || '');
    document.getElementById('detAgendaMotivoInput').value = '';
    document.getElementById('detAgendaEstado').innerHTML =
        "<span class='badge-estado-detalle badge-" + evento.estado + "'>" + evento.estado + "</span>";

    document.getElementById('btnConfirmAgendamiento').style.display= "";
    document.getElementById('btnConfirmDeudaAgendamiento').style.display= "none";
    document.getElementById('overlayDetalleAgenda').style.display = '';
    document.getElementById('modalDetalleAgenda').style.display = '';

    // Completa los datos del form de paciente
    idAbmCliente= evento.cod_cliente;
    document.getElementById('inptNombreApellidoCliente').value= evento.paciente || '';
    document.getElementById('inptNroDocCliente').value= evento.ci_cliente.replace(/\B(?=(\d{3})+(?!\d))/g, ".") || '';
    document.getElementById('inptNroRucCliente').value= evento.rut_cliente || '';
    document.getElementById('inptNrowhatsappCliente').value= evento.whapp || '';
    document.getElementById('inptZonaCliente').value= evento.nombre_zona || '';
    document.getElementById('inptFechaNacCliente').value= evento.fechanac || '';
    idFKZona = evento.idzonaFk || '';

    // Evalua si el cliente tiene cuotas pendientes
    document.getElementById('detAgendaDeudasPendientes').innerHTML = 'Cargando...'
    ventanaAnterior.push('divAgendaConsultorios');
    buscarCuentasPendientes('', '', evento.paciente, evento.ci_cliente, '', '', '', '','' ,'', '');
    document.getElementById('table_historial_paciente_agenda_detalle').innerHTML = 'Cargando...'
    buscarHistorialPacienteCalendario('detalle_agendamiento');
    obtenerComentariosAgendamiento();
}

function cerrarDetalleAgenda(){
    document.getElementById('overlayDetalleAgenda').style.display = 'none';
    document.getElementById('modalDetalleAgenda').style.display = 'none';
}

function filtrarAgendaLocal(){
    var texto = document.getElementById('inptBuscarPacienteAgenda').value.toLowerCase();
    var bloques = document.getElementsByClassName('agenda-evento');
    var i, paciente, ciCliente, pacienteElementos, ciClienteElementos;

    for(i = 0; i < bloques.length; i++){
        pacienteElementos = bloques[i].getElementsByClassName('paciente');
        ciClienteElementos = bloques[i].getElementsByClassName('ci_cliente');
        paciente = pacienteElementos.length > 0 ? pacienteElementos[0].innerHTML.toLowerCase() : '';
        ciCliente = ciClienteElementos.length > 0 ? ciClienteElementos[0].innerHTML.toLowerCase() : '';

        bloques[i].style.display = (paciente.indexOf(texto) >= 0 || ciCliente.indexOf(texto) >= 0) ? 'block' : 'none';
    }
}

function minimizarAgendaConsultorios(){
    $("div[id=divAgendaConsultorios]").fadeOut(500);
    document.getElementById("divMinimizadoCalendario").style.display = "";
}

function cerrarAgendaConsultorios(){
    document.getElementById('divAgendaConsultorios').style.display = 'none';
}

function AbrirAgendaConsultorios(ir_hoy= true){
    document.getElementById('divAgendaConsultorios').style.display = '';
    if (ir_hoy) {
        var hoy = new Date();
        document.getElementById('inptFechaAgenda').value = formatearFechaInput(hoy);
    }
    cargarAgendaConsultoriosDesdePHP();
}

/* ===========================
   DRAG AND DROP
=========================== */

function inicializarDragAndDropAgenda(){
    var eventos = document.querySelectorAll('.agenda-evento');
    var slots = document.querySelectorAll('.agenda-slot');
    var i;

    for(i = 0; i < eventos.length; i++){
        eventos[i].setAttribute('draggable', 'true');
        eventos[i].addEventListener('dragstart', onAgendaDragStart, false);
        eventos[i].addEventListener('dragend', onAgendaDragEnd, false);
        eventos[i].addEventListener('mousedown', function(ev){
            if(ev.target && ev.target.classList.contains('agenda-evento-resize')){
                return;
            }
            this.setAttribute('draggable', 'true');
        }, false);
    }

    for(i = 0; i < slots.length; i++){
        slots[i].addEventListener('dragover', onAgendaDragOver, false);
        slots[i].addEventListener('dragenter', onAgendaDragEnter, false);
        slots[i].addEventListener('dragleave', onAgendaDragLeave, false);
        slots[i].addEventListener('drop', onAgendaDrop, false);
    }

    inicializarResizeAgenda();
}

function onAgendaDragStart(ev){
    if(agendaResizeState.activo){
        ev.preventDefault();
        return;
    }

    var id = this.getAttribute('data-id');
    if(!id){
        ev.preventDefault();
        return;
    }

    agendaDragState.eventoId = id;
    this.classList.add('dragging-evento');

    if(ev.dataTransfer){
        ev.dataTransfer.setData('text/plain', id);
        ev.dataTransfer.effectAllowed = 'move';
    }
}

function onAgendaDragEnd(){
    this.classList.remove('dragging-evento');
    limpiarHoverSlotsAgenda();
    agendaDragState.eventoId = null;
}

function onAgendaDragOver(ev){
    ev.preventDefault();
    if(ev.dataTransfer){
        ev.dataTransfer.dropEffect = 'move';
    }
}

function onAgendaDragEnter(ev){
    ev.preventDefault();
    this.classList.add('agenda-slot-hover');
}

function onAgendaDragLeave(){
    this.classList.remove('agenda-slot-hover');
}

function onAgendaDrop(ev){
    ev.preventDefault();
    this.classList.remove('agenda-slot-hover');

    var id = agendaDragState.eventoId;
    if(!id && ev.dataTransfer){
        id = ev.dataTransfer.getData('text/plain');
    }

    if(!id){
        return;
    }

    moverEventoDesdeDrop(id, this, ev);
    limpiarHoverSlotsAgenda();
    agendaDragState.eventoId = null;
}

function limpiarHoverSlotsAgenda(){
    var slots = document.querySelectorAll('.agenda-slot');
    var i;
    for(i = 0; i < slots.length; i++){
        slots[i].classList.remove('agenda-slot-hover');
    }
}

function obtenerEventoPorId(id){
    var i;
    for(i = 0; i < agendaConsultoriosData.eventos.length; i++){
        if(String(agendaConsultoriosData.eventos[i].id) === String(id)){
            return agendaConsultoriosData.eventos[i];
        }
    }
    return null;
}

function horasSeSolapan(inicio1, fin1, inicio2, fin2){
    var ini1 = horaAMinutos(inicio1);
    var fin1m = horaAMinutos(fin1);
    var ini2 = horaAMinutos(inicio2);
    var fin2m = horaAMinutos(fin2);

    return ini1 < fin2m && fin1m > ini2;
}

/* ===========================
   RESIZE / ESTIRAR EVENTO
=========================== */

function inicializarResizeAgenda(){
    var handles = document.querySelectorAll('.agenda-evento-resize');
    var i;

    for(i = 0; i < handles.length; i++){
        handles[i].addEventListener('mousedown', onAgendaResizeStart, false);
    }

    document.removeEventListener('mousemove', onAgendaResizing, false);
    document.removeEventListener('mouseup', onAgendaResizeEnd, false);

    document.addEventListener('mousemove', onAgendaResizing, false);
    document.addEventListener('mouseup', onAgendaResizeEnd, false);
}

function onAgendaResizeStart(ev){
    ev.preventDefault();
    ev.stopPropagation();

    var id = this.getAttribute('data-id');
    var evento = obtenerEventoPorId(id);
    var bloqueEvento = this.parentNode;

    if(!evento || !bloqueEvento){
        return;
    }

    bloqueEvento.setAttribute('draggable', 'false');

    agendaResizeState.activo = true;
    agendaResizeState.eventoId = id;
    agendaResizeState.startY = ev.clientY;
    agendaResizeState.finOriginal = evento.fin;
    agendaResizeState.inicioOriginal = evento.inicio;
    agendaResizeState.consultorioOriginal = evento.consultorio;
    agendaResizeState.fechaOriginal = evento.fecha;
    agendaResizeState.elemento = bloqueEvento;
    agendaResizeState.clickBloqueado = true;

    bloqueEvento.classList.add('redimensionando-evento');
}

function onAgendaResizing(ev){
    var evento, diferenciaPx, diferenciaMinutos, nuevaDuracion, minInicio, minNuevoFin;
    var alturaPreview, spanHora;

    if(!agendaResizeState.activo){
        return;
    }

    evento = obtenerEventoPorId(agendaResizeState.eventoId);
    if(!evento || !agendaResizeState.elemento){
        return;
    }

    diferenciaPx = ev.clientY - agendaResizeState.startY;
    diferenciaMinutos = Math.round((diferenciaPx / 90) * 60);
    diferenciaMinutos = Math.round(diferenciaMinutos / 15) * 15;

    minInicio = horaAMinutos(agendaResizeState.inicioOriginal);
    minNuevoFin = horaAMinutos(agendaResizeState.finOriginal) + diferenciaMinutos;

    if(minNuevoFin <= minInicio){
        minNuevoFin = minInicio + 15;
    }

    if(minNuevoFin > (22 * 60 + 59)){
        minNuevoFin = 22 * 60 + 45;
    }

    nuevaDuracion = minNuevoFin - minInicio;
    alturaPreview = Math.max(18, Math.round((nuevaDuracion / 60) * 90) - 4);

    agendaResizeState.elemento.style.height = alturaPreview + 'px';
    agendaResizeState.elemento.setAttribute('data-fin', minutosAHora(minNuevoFin));

    spanHora = agendaResizeState.elemento.querySelector('.hora');
    if(spanHora){
        spanHora.innerHTML = agendaResizeState.inicioOriginal + " - " + minutosAHora(minNuevoFin);
    }
}
function resetAgendaResizeState(){
    if(agendaResizeState.elemento){
        agendaResizeState.elemento.classList.remove('redimensionando-evento');
        agendaResizeState.elemento.setAttribute('draggable', 'true');
    }

    agendaResizeState.activo = false;
    agendaResizeState.eventoId = null;
    agendaResizeState.startY = 0;
    agendaResizeState.finOriginal = '';
    agendaResizeState.inicioOriginal = '';
    agendaResizeState.consultorioOriginal = '';
    agendaResizeState.fechaOriginal = '';
    agendaResizeState.elemento = null;

    setTimeout(function(){
        agendaResizeState.clickBloqueado = false;
    }, 120);
}

function guardarMovimientoAgendaServidor(data){
    obtener_datos_user();

    var datos = {
        "useru": userid,
        "passu": passuser,
        "navegador": navegador,
        "funt": "moverCita",
        "id_agenda": data.id_agenda,
        "id_consultorio": data.id_consultorio,
        "fecha": data.fecha,
        "hora_inicio": data.hora_inicio,
        "hora_fin": data.hora_fin
    };

	verCerrarEfectoCargando("1");
    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmCalendar.php",
        type: "post",
        error: function (jqXHR, textstatus, errorThrowm) {
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
	        verCerrarEfectoCargando();
            cargarAgendaConsultoriosDesdePHP();
        },
        success: function (responseText) {
	        verCerrarEfectoCargando();
            try {
                var resp = responseText;

                if (typeof responseText === "string") {
                    resp = $.parseJSON(responseText);
                }

                var Respuesta = resp["1"];
                Respuesta = respuestaJqueryAjax(Respuesta);
				console.log(Respuesta)
                if (Respuesta == true) {
                    cargarAgendaConsultoriosDesdePHP();
                } else {
                    alert(resp["mensaje"] || "No se pudo mover la cita.");
                    cargarAgendaConsultoriosDesdePHP();
                }
            } catch (error) {
                var titulo = "Error mover cita: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
                cargarAgendaConsultoriosDesdePHP();
            }
        }
    });
}

function guardarResizeAgendaServidor(data){
    obtener_datos_user();

    var datos = {
        "useru": userid,
        "passu": passuser,
        "navegador": navegador,
        "funt": "redimensionarCita",
        "id_agenda": data.id_agenda,
        "hora_fin": data.hora_fin
    };

	verCerrarEfectoCargando("1");
    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmCalendar.php",
        type: "post",
        error: function (jqXHR, textstatus, errorThrowm) {
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
	        verCerrarEfectoCargando();
            cargarAgendaConsultoriosDesdePHP();
        },
        success: function (responseText) {
	        verCerrarEfectoCargando();
            try {
                var resp = responseText;

                if (typeof responseText === "string") {
                    resp = $.parseJSON(responseText);
                }

                var Respuesta = resp["1"];
                Respuesta = respuestaJqueryAjax(Respuesta); 
                if (Respuesta == true) {
                    cargarAgendaConsultoriosDesdePHP();
                } else {
                    alert(resp["mensaje"] || "No se pudo actualizar el horario.");
                    cargarAgendaConsultoriosDesdePHP();
                }
            } catch (error) {
                var titulo = "Error redimensionar cita: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
                cargarAgendaConsultoriosDesdePHP();
            }
        }
    });
}

function moverEventoDesdeDrop(idEvento, slot, ev){
    var evento = obtenerEventoPorId(idEvento);
    if(!evento){
        alert('No se encontró el evento.');
        return;
    }

    var nuevoConsultorio = slot.getAttribute('data-consultorio');
    var nuevaFecha = slot.getAttribute('data-fecha');
    var duracion = horaAMinutos(evento.fin) - horaAMinutos(evento.inicio);
    var nuevoInicio = calcularHoraSlotQuinceMinutos(slot, ev);
    var nuevoFin = minutosAHora(horaAMinutos(nuevoInicio) + duracion);

    if(horaAMinutos(nuevoFin) > (22 * 60 + 59)){
        alert('La cita no puede terminar fuera del horario visible.');
        return;
    }

    evento.consultorio = parseInt(nuevoConsultorio, 10);
    evento.fecha = nuevaFecha;
    evento.inicio = nuevoInicio;
    evento.fin = nuevoFin;

    guardarMovimientoAgendaServidor({
        id_agenda: evento.id,
        id_consultorio: evento.consultorio,
        fecha: evento.fecha,
        hora_inicio: evento.inicio,
        hora_fin: evento.fin
    });
}

function onAgendaResizeEnd(ev){
    var evento, diferenciaPx, diferenciaMinutos, minInicio, minNuevoFin, nuevoFin, nuevaAltura;
    var spanHora;

    if(!agendaResizeState.activo){
        return;
    }

    evento = obtenerEventoPorId(agendaResizeState.eventoId);

    if(!evento){
        resetAgendaResizeState();
        cargarAgendaConsultoriosDesdePHP();
        return;
    }

    diferenciaPx = ev.clientY - agendaResizeState.startY;
    diferenciaMinutos = Math.round((diferenciaPx / 90) * 60);
    diferenciaMinutos = Math.round(diferenciaMinutos / 15) * 15;

    minInicio = horaAMinutos(agendaResizeState.inicioOriginal);
    minNuevoFin = horaAMinutos(agendaResizeState.finOriginal) + diferenciaMinutos;

    if(minNuevoFin <= minInicio){
        minNuevoFin = minInicio + 15;
    }

    if(minNuevoFin > (22 * 60 + 59)){
        minNuevoFin = 22 * 60 + 45;
    }

    nuevoFin = minutosAHora(minNuevoFin);
    nuevaAltura = Math.max(18, Math.round(((minNuevoFin - minInicio) / 60) * 90) - 4);

    evento.fin = nuevoFin;

    if(agendaResizeState.elemento){
        agendaResizeState.elemento.style.height = nuevaAltura + 'px';
        agendaResizeState.elemento.setAttribute('data-fin', nuevoFin);
        agendaResizeState.elemento.setAttribute('draggable', 'true');

        spanHora = agendaResizeState.elemento.querySelector('.hora');
        if(spanHora){
            spanHora.innerHTML = evento.inicio + " - " + evento.fin;
        }

        agendaResizeState.elemento.classList.remove('redimensionando-evento');
    }

    guardarResizeAgendaServidor({
        id_agenda: evento.id,
        hora_fin: evento.fin
    });

    resetAgendaResizeState();
}

function cambiarEstadoAgendaDesdeModal(nuevoEstado){
    var idAgenda = document.getElementById('detAgendaId').innerHTML;

    if(idAgenda == ''){
        alert('No se encontró el ID del agendamiento.');
        return;
    }

    actualizarAgenda(idAgenda, '', '', nuevoEstado);
}

function actualizarHorarioAgendaDesdeModal(){
    var idAgenda = document.getElementById('detAgendaId').innerHTML;
    var horaInicio = document.getElementById('detAgendaHorarioInicio').value;
    var horaFin = document.getElementById('detAgendaHorarioFin').value;

    if(idAgenda == ''){
        alert('No se encontró el ID del agendamiento.');
        return;
    }

    if(horaInicio == '' || horaFin == ''){
        alert('Debe cargar la hora de inicio y fin.');
        return;
    }

    if(horaAMinutos(horaFin) <= horaAMinutos(horaInicio)){
        alert('La hora fin debe ser mayor a la hora inicio.');
        return;
    }

    actualizarAgenda(idAgenda, horaInicio, horaFin, '');
}

function actualizarAgenda(idAgenda, horaInicio, horaFin, estado){
    obtener_datos_user();

    var datos = {
        "useru": userid,
        "passu": passuser,
        "navegador": navegador,
        "funt": "actualizarCita",
        "estado": estado,
        "id_agenda": idAgenda,
        "hora_inicio": horaInicio,
        "hora_fin": horaFin
    };

	verCerrarEfectoCargando("1");
    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmCalendar.php",
        type: "post",
        error: function (jqXHR, textstatus, errorThrowm) {
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
        	verCerrarEfectoCargando();
        },
        success: function (responseText) {
	        verCerrarEfectoCargando();
            try {
                var resp = responseText;

                if (typeof responseText === "string") {
                    resp = $.parseJSON(responseText);
                }

                var Respuesta = resp["1"];
                Respuesta = respuestaJqueryAjax(Respuesta);

                if (Respuesta == true) {
                    cerrarDetalleAgenda();
                    cargarAgendaConsultoriosDesdePHP();
                    ventanaAnterior.pop();
                } else {
                    alert(resp["mensaje"] || "No se pudo actualizar el horario.");
                }
            } catch (error) {
                var titulo = "Error actualizar horario agenda: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
                console.log(responseText);
            }
        }
    });
}

function asignarCodPresupuestoAgenda(){
    obtener_datos_user();
    var datos = {
        "useru": userid,
        "passu": passuser,
        "navegador": navegador,
        "id_agenda": idAbmAgenda,
        "cod_presupuestoFK": idabmPresupuesto,
        "funt": "actualizarPresupuestoAgenda"
    };

	verCerrarEfectoCargando("1");
    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmCalendar.php",
        type: "post",
        beforeSend: function () {
        },
        error: function (jqXHR, textstatus, errorThrowm) {
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
	        verCerrarEfectoCargando();
        },
        success: function (responseText) {
	        verCerrarEfectoCargando();
            try {
                var resp = responseText;
                var Respuesta = resp["1"];
                Respuesta = respuestaJqueryAjax(Respuesta);
                if(Respuesta = true){
                    cambiarEstadoAgendaDesdeModal("ATENDIDO");
                } else {
                    ver_vetana_informativa("No se pudo vincular el presupuesto con la agenda");
                }
            } catch (error) {
                var titulo = "Error guardarCitaAgenda: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
                console.log(responseText);
            }
        }
    });
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function guardarCitaAgenda(){
    obtener_datos_user();

    var paciente = document.getElementById('inptIdPacienteAgenda').value;
    var consultorio = document.getElementById('inptConsultorioAgenda').value;
    var fecha = document.getElementById('inptFechaNuevaCita').value;
    var inicio = document.getElementById('inptHoraInicioAgenda').value;
    var fin = document.getElementById('inptHoraFinAgenda').value;
    var estado = document.getElementById('inptEstadoNuevaCita').value;
    var motivo = obtenerMotivoCitaAgendaConCreador('inptMotivoAgenda');

    if(paciente == ''){
        alert('Debe seleccionar un paciente');
        return;
    }

    if(consultorio == ''){
        alert('Debe seleccionar el consultorio');
        return;
    }

    if(fecha == ''){
        alert('Debe cargar la fecha');
        return;
    }

    if(esDomingoFechaAgenda(fecha)){
        alert('La fecha seleccionada es domingo');
        return;
    }

    if(inicio == ''){
        alert('Debe cargar la hora de inicio');
        return;
    }

    if(fin == ''){
        alert('Debe cargar la hora de fin');
        return;
    }

    if(horaAMinutos(fin) <= horaAMinutos(inicio)){
        alert('La hora fin debe ser mayor a la hora inicio');
        return;
    }

    document.getElementById('btnGuardarCitaAgenda').disabled= true;
    var datos = {
        "useru": userid,
        "passu": passuser,
        "navegador": navegador,
        "paciente": paciente,
        "consultorio": consultorio,
        "fecha": fecha,
        "inicio": inicio,
        "fin": fin,
        "estado": estado,
        "motivo": motivo,
        "funt": "guardarCita"
    };

	verCerrarEfectoCargando("1");
    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmCalendar.php",
        type: "post",
        beforeSend: function () {
        },
        error: function (jqXHR, textstatus, errorThrowm) {
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
	        verCerrarEfectoCargando();
            document.getElementById('btnGuardarCitaAgenda').disabled= true;
        },
        success: function (responseText) {
	        verCerrarEfectoCargando();
            try {
                var resp = responseText;

                if(typeof responseText === "string"){
                    resp = $.parseJSON(responseText);
                }

                var Respuesta = resp["1"];
                Respuesta = respuestaJqueryAjax(Respuesta);

                if(Respuesta == true){
                    vercerrarModalNuevaCita(false);
                    limpiarFormularioNuevaCita();
                    cargarAgendaConsultoriosDesdePHP();
                }else{
                    alert(resp["mensaje"] || "No se pudo guardar la cita");
                }
                document.getElementById('btnGuardarCitaAgenda').disabled= false;
            } catch (error) {
                var titulo = "Error guardarCitaAgenda: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
                console.log(responseText);
                document.getElementById('btnGuardarCitaAgenda').disabled= false;
            }
        }
    });
}

function limpiarFormularioNuevaCita(){
    document.getElementById('inptPacienteAgenda').value = '';
    document.getElementById('inptConsultorioAgenda').value = '';
    document.getElementById('inptFechaNuevaCita').value = '';
    document.getElementById('inptHoraInicioAgenda').value = '';
    document.getElementById('inptHoraFinAgenda').value = '';
    document.getElementById('inptEstadoNuevaCita').value = 'AGENDADO';
    document.getElementById('inptMotivoAgenda').value = '';
}

function verCerrarModalBuscarPacienteAgenda(mostrar) {
    if (mostrar) {
        document.getElementById('overlayBuscarPacienteAgenda').style.display = 'block';
        document.getElementById('modalBuscarPacienteAgenda').style.display = 'block';
        document.getElementById('inptBuscarPacienteAgendaModal').focus();
        buscarPacientesAgenda();
    } else {
        document.getElementById('overlayBuscarPacienteAgenda').style.display = 'none';
        document.getElementById('modalBuscarPacienteAgenda').style.display = 'none';
    }
}

function verCerrarModalNuevoPacienteAgenda(mostrar) {
    if (mostrar) {
        document.getElementById('overlayNuevoPacienteAgenda').style.display = 'block';
        document.getElementById('modalNuevoPacienteAgenda').style.display = 'block';
    } else {
        document.getElementById('overlayNuevoPacienteAgenda').style.display = 'none';
        document.getElementById('modalNuevoPacienteAgenda').style.display = 'none';
    }
}

function buscarPacientesAgenda(){
    obtener_datos_user();

    var buscar = document.getElementById('inptBuscarPacienteAgendaModal').value;

    var datos = {
        "useru": userid,
        "passu": passuser,
        "navegador": navegador,
        "buscar": buscar,
        "funt": "buscarPacientesAgenda"
    };

    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmCalendar.php",
        type: "post",
        beforeSend: function () {
            document.getElementById("divTablaPacientesAgenda").innerHTML = paginacargando;
        },
        error: function (jqXHR, textstatus, errorThrowm) {
            document.getElementById("divTablaPacientesAgenda").innerHTML = '';
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
        },
        success: function (responseText) {
            try {
                var resp = responseText;

                if (typeof responseText === "string") {
                    resp = $.parseJSON(responseText);
                }

                if (resp["1"] == "exito") {
                    document.getElementById("divTablaPacientesAgenda").innerHTML = resp["2"];
                } else {
                    document.getElementById("divTablaPacientesAgenda").innerHTML = "";
                }
            } catch (error) {
                var titulo = "Error buscarPacientesAgenda: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
                document.getElementById("divTablaPacientesAgenda").innerHTML = "";
            }
        }
    });
}

function seleccionarPacienteAgenda(idPaciente, nombrePaciente){
    document.getElementById('inptIdPacienteAgenda').value = idPaciente;
    document.getElementById('inptPacienteAgenda').value = nombrePaciente;
    verCerrarModalBuscarPacienteAgenda(false);
}

function guardarNuevoPacienteAgenda(){
    obtener_datos_user();

    var nombre = document.getElementById('inptNombreNuevoPacienteAgenda').value;
    var documento = document.getElementById('inptDocumentoNuevoPacienteAgenda').value;
    var telefono = document.getElementById('inptTelefonoNuevoPacienteAgenda').value;
    var direccion = document.getElementById('inptDireccionNuevoPacienteAgenda').value;

    if(nombre == ''){
        alert('Debe cargar el nombre del paciente');
        return;
    }

    var datos = {
        "useru": userid,
        "passu": passuser,
        "navegador": navegador,
        "nombre": nombre,
        "documento": documento,
        "telefono": telefono,
        "direccion": direccion,
        "funt": "guardarPacienteAgenda"
    };

	verCerrarEfectoCargando("1");
    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmCalendar.php",
        type: "post",
        error: function (jqXHR, textstatus, errorThrowm) {
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
	        verCerrarEfectoCargando();
        },
        success: function (responseText) {
	        verCerrarEfectoCargando();
            try {
                var resp = responseText;

                if (typeof responseText === "string") {
                    resp = $.parseJSON(responseText);
                }

                if (resp["1"] == "exito") {
                    document.getElementById('inptIdPacienteAgenda').value = resp["id_paciente"];
                    document.getElementById('inptPacienteAgenda').value = resp["nombre_paciente"];

                    document.getElementById('inptNombreNuevoPacienteAgenda').value = '';
                    document.getElementById('inptDocumentoNuevoPacienteAgenda').value = '';
                    document.getElementById('inptTelefonoNuevoPacienteAgenda').value = '';
                    document.getElementById('inptDireccionNuevoPacienteAgenda').value = '';

                    verCerrarModalNuevoPacienteAgenda(false);
                } else {
                    alert(resp["mensaje"] || "No se pudo guardar el paciente");
                }
            } catch (error) {
                var titulo = "Error guardarNuevoPacienteAgenda: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
            }
        }
    });
}


document.addEventListener("DOMContentLoaded", function () {
    var temaGuardado = localStorage.getItem("tema");
    var btn = document.getElementById("btnTema");

    if (temaGuardado === "dark") {
        document.body.classList.add("theme-dark");
        if(btn) btn.innerHTML = "☀️";
    } else {
        if(btn) btn.innerHTML = "🌙";
    }
});

function toggleTema() {
    var body = document.body;
    var btn = document.getElementById("btnTema");

    if (body.classList.contains("theme-dark")) {
        body.classList.remove("theme-dark");
        localStorage.setItem("tema", "light");
        if(btn) btn.innerHTML = "🌙";
    } else {
        body.classList.add("theme-dark");
        localStorage.setItem("tema", "dark");
        if(btn) btn.innerHTML = "☀️";
    }
}

function buscarHistorialPacienteCalendario(controlVentana) {
    let paciente= '';
    let tabla;
    switch (controlVentana) {
        case 'filtro':
            paciente = document.getElementById('inptBuscarPacienteAgenda').value || '';
            tabla = document.getElementById("table_historial_paciente_agenda");
            var seccion = document.getElementById("seccion_historial_agenda");

            if(paciente.trim() == ''){
                seccion.style.display = "none";
                seccion.parentElement.style.gridTemplateColumns = '1fr';
                tabla.innerHTML = "";
                return;
            }

            seccion.style.display = "flex";
            seccion.parentElement.style.gridTemplateColumns= 'repeat(2, 1fr)';
            break;
        case 'detalle_agendamiento':
            paciente = document.getElementById('detAgendaCedula').textContent.replaceAll('.', '') || '';
            tabla = document.getElementById("table_historial_paciente_agenda_detalle");
            
            if(paciente.trim() == ''){
                tabla.innerHTML= "No se encontro ningun agendamiento anteriormente.";
                //ver_vetana_informativa("Faltan datos", "Cedula no es valido", "advertencia");
                return;
            }

            break;
    }
    
    obtener_datos_user();
    var datos = {
        "useru": userid,
        "passu": passuser,
        "navegador": navegador,
        "paciente": paciente,
        "funt": "buscarHistorialPacienteCalendario"
    };

    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmCalendar.php",
        type: "post",
        beforeSend: function () {
            tabla.innerHTML = paginacargando;
        },
        error: function (jqXHR, textstatus, errorThrowm) {
            tabla.innerHTML = "";
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
        },
        success: function (responseText) {
            try {
                var resp = responseText;

                if (typeof responseText === "string") {
                    resp = $.parseJSON(responseText);
                }

                if (resp["1"] == "exito") {
                    if (resp["3"] == 1) {
                        tabla.innerHTML = resp["2"];
                    } else {
                        ver_vetana_informativa("Se encontro "+resp["3"]+" pacientes con el mismo CI o nombre en agendamientos anteriores.");
                        tabla.innerHTML = "";
                    }
                } else {
                    tabla.innerHTML = "";
                }
            } catch (error) {
                var titulo = "Error buscarHistorialPacienteCalendario: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
                tabla.innerHTML = "";
            }
        }
    });
}

function verHistorialDesdeConsultorio() {
    const cedula= document.getElementById('detAgendaCedula').textContent.replaceAll('.', '');

    if (cedula) {
        cerrarDetalleAgenda();
        cerrarAgendaConsultorios();
        verCerrarAbmVistaConsulta('consulta');
        document.getElementById('inptBuscarFrmPacienteVistaConsulta').value= cedula;
        buscarVistaConsulta();
    } else {
        ver_vetana_informativa("El paciente no tiene cedula", "", "info");
    }
}

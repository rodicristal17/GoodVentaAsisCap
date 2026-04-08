var agendaConsultoriosData = {
    consultorios: [],
    eventos: []
};

function cargarAgendaConsultoriosDesdePHP() {
    obtener_datos_user();

    var fecha = document.getElementById('inptFechaAgenda').value || '';

    var datos = {
        "useru": userid,
        "passu": passuser,
        "navegador": navegador,
        "fecha": fecha,
        "funt": "cargarAgenda"
    };

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
        beforeSend: function () {
            // document.getElementById("agendaGridConsultorios").innerHTML = paginacargando;
        },
        error: function (jqXHR, textstatus, errorThrowm) {
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
            document.getElementById("agendaGridConsultorios").innerHTML = '';
        },
        success: function (responseText) {
            try {
                var datosRespuesta = responseText;
					console.log(datosRespuesta)
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

    for(i = 0; i < agendaConsultoriosData.consultorios.length; i++){
        c = agendaConsultoriosData.consultorios[i];
        html += ''
        + "<div class='item-consultorio'>"
            + "<span class='consultorio-color' style='background:" + c.color + "'></span>"
            + "<div><b>" + c.nombre + "</b><br><span style='color:#6b7c90;font-size:11px'>" + c.descripcion + "</span></div>"
        + "</div>";
    }

    cont.innerHTML = html;
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

function cargarAgendaConsultorios(){
    var fecha = document.getElementById('inptFechaAgenda').value;
    var estado = document.getElementById('inptEstadoAgenda').value;
    var consultorioFiltro = document.getElementById('inptConsultorioAgendaFiltro')
        ? document.getElementById('inptConsultorioAgendaFiltro').value
        : '';

    var consultorios = [];
    var i, j, hora;
    var html = '';
    var esHoraAlmuerzo = false;
    var claseHora = '';
    var claseSlot = '';

    for(i = 0; i < agendaConsultoriosData.consultorios.length; i++){
        if(consultorioFiltro === '' || String(agendaConsultoriosData.consultorios[i].id) === String(consultorioFiltro)){
            consultorios.push(agendaConsultoriosData.consultorios[i]);
        }
    }

    html += "<div class='agenda-header-row' style='--total-consultorios:" + consultorios.length + "'>";
    html += "<div class='agenda-celda-hora-header'>Hora</div>";

    for(i = 0; i < consultorios.length; i++){
        html += "<div class='agenda-celda-consultorio'>"
            + consultorios[i].nombre
            + "<span class='agenda-consultorio-sub'>" + consultorios[i].descripcion + "</span>"
        + "</div>";
    }

    html += "</div>";

    for(hora = 7; hora <= 22; hora++){
        esHoraAlmuerzo = (hora >= 12 && hora < 14);

        claseHora = esHoraAlmuerzo ? " agenda-hora-almuerzo" : "";
        claseSlot = esHoraAlmuerzo ? " agenda-slot-almuerzo" : "";

        html += "<div class='agenda-row' style='--total-consultorios:" + consultorios.length + "'>";
        html += "<div class='agenda-hora" + claseHora + "'>" + completarHora(hora) + ":00</div>";

        for(j = 0; j < consultorios.length; j++){
            html += "<div class='agenda-slot" + claseSlot + "' "
                + "data-consultorio='" + consultorios[j].id + "' "
                + "data-fecha='" + fecha + "' "
                + "data-hora='" + completarHora(hora) + ":00'"
                + "></div>";
        }

        html += "</div>";
    }

    document.getElementById('agendaGridConsultorios').innerHTML = html;

    pintarEventosAgenda(fecha, estado, consultorioFiltro);
    actualizarResumenAgenda(fecha, estado, consultorioFiltro);
    actualizarResumenFiltrosAgenda();
    inicializarDragAndDropAgenda();
}
function pintarEventosAgenda(fecha, estado, consultorioFiltro){
    var slots = document.querySelectorAll('.agenda-slot');
    var i, j, slot, consultorioId, horaSlot, eventosConsultorio, htmlEventos;

    for(i = 0; i < slots.length; i++){
        slot = slots[i];
        consultorioId = slot.getAttribute('data-consultorio');
        horaSlot = slot.getAttribute('data-hora');

        eventosConsultorio = obtenerEventosFiltradosConsultorio(fecha, estado, consultorioFiltro, consultorioId);
        htmlEventos = '';

        for(j = 0; j < eventosConsultorio.length; j++){
            if(obtenerHoraTexto(eventosConsultorio[j].inicio) === horaSlot){
                htmlEventos += renderEventoAgenda(eventosConsultorio[j], eventosConsultorio);
            }
        }

        slot.innerHTML = htmlEventos;
    }
}

function obtenerEventosFiltradosConsultorio(fecha, estado, consultorioFiltro, consultorioId){
    var lista = [];
    var i, ev;

    for(i = 0; i < agendaConsultoriosData.eventos.length; i++){
        ev = agendaConsultoriosData.eventos[i];
        if(
            ev.fecha === fecha &&
            String(ev.consultorio) === String(consultorioId) &&
            (estado === '' || ev.estado === estado) &&
            (consultorioFiltro === '' || String(ev.consultorio) === String(consultorioFiltro))
        ){
            lista.push(ev);
        }
    }

    lista.sort(function(a, b){
        var diff = horaAMinutos(a.inicio) - horaAMinutos(b.inicio);
        if(diff !== 0){
            return diff;
        }
        return horaAMinutos(a.fin) - horaAMinutos(b.fin);
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
        MiColor = "#ffc107";
    }else if(e.estado == "CONFIRMADO"){
        MiColor = "#198754";
    }else if(e.estado == "CANCELADO"){
        MiColor = "#6c757d";
    }else if(e.estado == "ATENDIDO"){
        MiColor = "#007bff";
    }

    var estilos = ""
        + "background:" + MiColor + ";"
        + "top:" + top + "px;"
        + "left:" + left + ";"
        + "width:" + width + ";"
        + "height:" + altura + "px;"
        + "right:auto;"
        + "overflow:visible;";

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
        + "<span class='paciente'>" + e.paciente + "</span>"
        + "<span class='hora'>" + e.inicio + " - " + e.fin + "</span>"
        + "<span class='detalle'>" + (e.motivo || '') + "</span>"
        + "<div class='agenda-evento-resize' "
            + "data-id='" + e.id + "' "
            + "title='Arrastrar para alargar o acortar horario'></div>"
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

function calcularTopEvento(inicio){
    var partes = inicio.split(':');
    var minutos = parseInt(partes[1], 10);
    return 6 + Math.round((minutos / 60) * 74);
}

function calcularAlturaEvento(inicio, fin){
    var min1 = horaAMinutos(inicio);
    var min2 = horaAMinutos(fin);
    var diferencia = min2 - min1;

    return Math.max(18, Math.round((diferencia / 60) * 74) - 4);
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
    return completarHora(parseInt(h.split(':')[0], 10)) + ":00";
}

function completarHora(n){
    return (n < 10 ? '0' : '') + n;
}

function actualizarResumenAgenda(fecha, estado, consultorioFiltro){
    var total = 0;
    var confirmadas = 0;
    var pendientes = 0;
    var canceladas = 0;
    var i, e;

    for(i = 0; i < agendaConsultoriosData.eventos.length; i++){
        e = agendaConsultoriosData.eventos[i];
        if(
            e.fecha === fecha &&
            (estado === '' || e.estado === estado) &&
            (consultorioFiltro === '' || String(e.consultorio) === String(consultorioFiltro))
        ){
            total++;
            if(e.estado === 'CONFIRMADO'){ confirmadas++; }
            if(e.estado === 'AGENDADO'){ pendientes++; }
            if(e.estado === 'CANCELADO'){ canceladas++; }
        }
    }

    document.getElementById('lblTotalCitasAgenda').innerHTML = total;
    document.getElementById('lblConfirmadasAgenda').innerHTML = confirmadas;
    document.getElementById('lblPendientesAgenda').innerHTML = pendientes;
    document.getElementById('lblCanceladasAgenda').innerHTML = canceladas;
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

function abrirModalFiltrosAgenda(){
    document.getElementById('overlayFiltrosAgenda').style.display = 'block';
    document.getElementById('modalFiltrosAgenda').style.display = 'block';
}

function cerrarModalFiltrosAgenda(){
    document.getElementById('overlayFiltrosAgenda').style.display = 'none';
    document.getElementById('modalFiltrosAgenda').style.display = 'none';
}

function aplicarFiltrosAgenda(){
    cerrarModalFiltrosAgenda();
    cargarAgendaConsultorios();
}

function limpiarFiltrosAgenda(){
    document.getElementById('inptConsultorioAgendaFiltro').value = '';

    if(document.getElementById('inptProfesionalAgendaFiltro')){
        document.getElementById('inptProfesionalAgendaFiltro').value = '';
    }

    document.getElementById('chkAgendaAgendado').checked = true;
    document.getElementById('chkAgendaConfirmado').checked = true;
    document.getElementById('chkAgendaAtendido').checked = true;
    document.getElementById('chkAgendaCancelado').checked = false;

    cargarAgendaConsultorios();
}

function abrirModalNuevaCita(){
	
	if(controlacceso("INSERTARFORMULARIOCALENDARIO","accion")==false){return;}
	 		
    document.getElementById('inptFechaNuevaCita').value = document.getElementById('inptFechaAgenda').value;
    document.getElementById('overlayNuevaCita').style.display = 'block';
    document.getElementById('modalNuevaCita').style.display = 'block';
}

function cerrarModalNuevaCita(){
    document.getElementById('overlayNuevaCita').style.display = 'none';
    document.getElementById('modalNuevaCita').style.display = 'none';
}

function guardarCitaAgenda(){
    var paciente = document.getElementById('inptPacienteAgenda').value;
    var consultorio = document.getElementById('inptConsultorioAgenda').value;
    var fecha = document.getElementById('inptFechaNuevaCita').value;
    var inicio = document.getElementById('inptHoraInicioAgenda').value;
    var fin = document.getElementById('inptHoraFinAgenda').value;
    var estado = document.getElementById('inptEstadoNuevaCita').value;
    var motivo = document.getElementById('inptMotivoAgenda').value;

    if(paciente === '' || consultorio === '' || fecha === '' || inicio === '' || fin === ''){
        alert('Complete los datos obligatorios de la cita.');
        return;
    }

    if(horaAMinutos(fin) <= horaAMinutos(inicio)){
        alert('La hora fin debe ser mayor a la hora inicio.');
        return;
    }

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

    cerrarModalNuevaCita();
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
}

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

    document.getElementById('detAgendaId').innerHTML = evento.id;
    document.getElementById('detAgendaPaciente').innerHTML = evento.paciente || '';
    document.getElementById('detAgendaConsultorio').innerHTML = nombreConsultorio;
    document.getElementById('detAgendaFecha').innerHTML = evento.fecha || '';
    document.getElementById('detAgendaHorario').innerHTML = (evento.inicio || '') + ' - ' + (evento.fin || '');
    document.getElementById('detAgendaMotivo').innerHTML = evento.motivo || '-';
    document.getElementById('detAgendaEstado').innerHTML =
        "<span class='badge-estado-detalle badge-" + evento.estado + "'>" + evento.estado + "</span>";

    document.getElementById('overlayDetalleAgenda').style.display = 'block';
    document.getElementById('modalDetalleAgenda').style.display = 'block';
}

function cerrarDetalleAgenda(){
    document.getElementById('overlayDetalleAgenda').style.display = 'none';
    document.getElementById('modalDetalleAgenda').style.display = 'none';
}

function filtrarAgendaLocal(){
    var texto = document.getElementById('inptBuscarPacienteAgenda').value.toLowerCase();
    var bloques = document.getElementsByClassName('agenda-evento');
    var i, paciente;

    for(i = 0; i < bloques.length; i++){
        paciente = bloques[i].getElementsByClassName('paciente')[0].innerHTML.toLowerCase();
        bloques[i].style.display = paciente.indexOf(texto) >= 0 ? 'block' : 'none';
    }
}

function minimizarAgendaConsultorios(){
    $("div[id=divAgendaConsultorios]").fadeOut(500);
    document.getElementById("divMinimizadoCalendario").style.display = "";
}

function cerrarAgendaConsultorios(){
    document.getElementById('divAgendaConsultorios').style.display = 'none';
}

function AbrirAgendaConsultorios(){
    irHoyAgenda();
    document.getElementById('divAgendaConsultorios').style.display = '';
}

/* ===========================
   DRAG AND DROP
=========================== */

function inicializarDragAndDropAgenda(){
    var eventos = document.querySelectorAll('.agenda-evento');
    var slots = document.querySelectorAll('.agenda-slot');
    var i;

    for(i = 0; i < eventos.length; i++){
        eventos[i].addEventListener('dragstart', onAgendaDragStart, false);
        eventos[i].addEventListener('dragend', onAgendaDragEnd, false);
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

 

function calcularHoraDrop(slot, ev){
    var horaBase = slot.getAttribute('data-hora');
    var minBase = horaAMinutos(horaBase);

    var rect = slot.getBoundingClientRect();
    var y = ev.clientY - rect.top;

    if(y < 0){
        y = 0;
    }
    if(y > rect.height){
        y = rect.height;
    }

    var proporcion = rect.height > 0 ? (y / rect.height) : 0;
    var minutosDentroHora = Math.round((proporcion * 60) / 15) * 15;

    if(minutosDentroHora >= 60){
        minutosDentroHora = 45;
    }

    return minutosAHora(minBase + minutosDentroHora);
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

function hayConflictoAgenda(idExcluir, consultorio, fecha, inicio, fin){
    return false;
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
    diferenciaMinutos = Math.round((diferenciaPx / 74) * 60);
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
    alturaPreview = Math.max(18, Math.round((nuevaDuracion / 60) * 74) - 4);

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

    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmCalendar.php",
        type: "post",
        error: function (jqXHR, textstatus, errorThrowm) {
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
            cargarAgendaConsultoriosDesdePHP();
        },
        success: function (responseText) {
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

    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmCalendar.php",
        type: "post",
        error: function (jqXHR, textstatus, errorThrowm) {
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
            cargarAgendaConsultoriosDesdePHP();
        },
        success: function (responseText) {
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
    var nuevoInicio = calcularHoraDrop(slot, ev);
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
    diferenciaMinutos = Math.round((diferenciaPx / 74) * 60);
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
    nuevaAltura = Math.max(18, Math.round(((minNuevoFin - minInicio) / 60) * 74) - 4);

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

    actualizarEstadoAgendaServidor(idAgenda, nuevoEstado);
}

function actualizarEstadoAgendaServidor(idAgenda, nuevoEstado){
    obtener_datos_user();

    var datos = {
        "useru": userid,
        "passu": passuser,
        "navegador": navegador,
        "funt": "actualizarEstadoCita",
        "id_agenda": idAgenda,
        "estado": nuevoEstado
    };

    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmCalendar.php",
        type: "post",
        error: function (jqXHR, textstatus, errorThrowm) {
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
        },
        success: function (responseText) {
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
                } else {
                    alert(resp["mensaje"] || "No se pudo actualizar el estado.");
                }
            } catch (error) {
                var titulo = "Error actualizar estado agenda: " + error + " \r\n Consola: " + responseText;
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
    var motivo = document.getElementById('inptMotivoAgenda').value;

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

    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmCalendar.php",
        type: "post",
        beforeSend: function () {
        },
        error: function (jqXHR, textstatus, errorThrowm) {
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
        },
        success: function (responseText) {
            try {
                var resp = responseText;

                if(typeof responseText === "string"){
                    resp = $.parseJSON(responseText);
                }

                var Respuesta = resp["1"];
                Respuesta = respuestaJqueryAjax(Respuesta);

                if(Respuesta == true){
                    cerrarModalNuevaCita();
                    limpiarFormularioNuevaCita();
                    cargarAgendaConsultoriosDesdePHP();
                }else{
                    alert(resp["mensaje"] || "No se pudo guardar la cita");
                }
            } catch (error) {
                var titulo = "Error guardarCitaAgenda: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
                console.log(responseText);
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





function abrirModalBuscarPacienteAgenda(){
    document.getElementById('overlayBuscarPacienteAgenda').style.display = 'block';
    document.getElementById('modalBuscarPacienteAgenda').style.display = 'block';
    document.getElementById('inptBuscarPacienteAgendaModal').focus();
    buscarPacientesAgenda();
}

function cerrarModalBuscarPacienteAgenda(){
    document.getElementById('overlayBuscarPacienteAgenda').style.display = 'none';
    document.getElementById('modalBuscarPacienteAgenda').style.display = 'none';
}

function abrirModalNuevoPacienteAgenda(){
    document.getElementById('overlayNuevoPacienteAgenda').style.display = 'block';
    document.getElementById('modalNuevoPacienteAgenda').style.display = 'block';
}

function cerrarModalNuevoPacienteAgenda(){
    document.getElementById('overlayNuevoPacienteAgenda').style.display = 'none';
    document.getElementById('modalNuevoPacienteAgenda').style.display = 'none';
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
    cerrarModalBuscarPacienteAgenda();
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

    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmCalendar.php",
        type: "post",
        error: function (jqXHR, textstatus, errorThrowm) {
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
        },
        success: function (responseText) {
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

                    cerrarModalNuevoPacienteAgenda();
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

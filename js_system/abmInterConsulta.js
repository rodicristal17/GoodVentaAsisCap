var controldebusquedadInformeInterConsulta= true;
var totalregistroinformeInterConsulta= 0;
var registrocargadoInterConsulta= 0;
var registroInterConsultaAbierta= 0;
var cod_interConsulta= "";
var temporizadorBusquedaGlobalInterConsulta= null;
var busquedaInterConsultaCancelada= false;

function valorCampoInterConsulta(id) {
    const elemento= document.getElementById(id);
    return elemento ? elemento.value : "";
}

function asignarValorCampoInterConsulta(id, valor) {
    const elemento= document.getElementById(id);
    if (elemento) {
        elemento.value= valor || "";
    }
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
    clonarOpcionesInterConsulta("inptBuscarInterConsulta7", "inptFiltroRapidoLocalInterConsulta");
    clonarOpcionesInterConsulta("inptUsuariosInterConsulta", "inptFiltroRapidoResponsableInterConsulta", true);
}

function sincronizarFiltrosInterConsultaDesdeBarra() {
    sincronizarOpcionesRapidasInterConsulta();
    asignarValorCampoInterConsulta("inptBuscarInterConsulta5", valorCampoInterConsulta("inptFiltroRapidoEstadoInterConsulta"));
    asignarValorCampoInterConsulta("inptBuscarInterConsulta4", valorCampoInterConsulta("inptFiltroRapidoTipoInterConsulta"));
    asignarValorCampoInterConsulta("inptBuscarInterConsulta7", valorCampoInterConsulta("inptFiltroRapidoLocalInterConsulta"));
    asignarValorCampoInterConsulta("inptBuscarInterConsulta6", valorCampoInterConsulta("inptFiltroRapidoResponsableInterConsulta"));
}

function sincronizarFiltrosInterConsultaDesdeAvanzado() {
    sincronizarOpcionesRapidasInterConsulta();
    asignarValorCampoInterConsulta("inptFiltroRapidoEstadoInterConsulta", valorCampoInterConsulta("inptBuscarInterConsulta5"));
    asignarValorCampoInterConsulta("inptFiltroRapidoTipoInterConsulta", valorCampoInterConsulta("inptBuscarInterConsulta4"));
    asignarValorCampoInterConsulta("inptFiltroRapidoLocalInterConsulta", valorCampoInterConsulta("inptBuscarInterConsulta7"));
    asignarValorCampoInterConsulta("inptFiltroRapidoResponsableInterConsulta", valorCampoInterConsulta("inptBuscarInterConsulta6"));
    actualizarChipActivoInterConsulta();
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
        "inptFiltroRapidoResponsableInterConsulta"
    ].forEach(function(id) {
        asignarValorCampoInterConsulta(id, "");
    });

    const ocultarInactivos= document.getElementById("inptSeleccFiltroEstadoInterConsulta");
    if (ocultarInactivos) {
        ocultarInactivos.checked= true;
    }

    actualizarChipActivoInterConsulta();
    if (ejecutarBusqueda) {
        buscarPacientesConInterConsultas();
    }
}

function aplicarChipInterConsulta(campo, valor= "") {
    if (campo == "todos") {
        asignarValorCampoInterConsulta("inptFiltroRapidoEstadoInterConsulta", "");
        asignarValorCampoInterConsulta("inptFiltroRapidoTipoInterConsulta", "");
    } else if (campo == "estado") {
        asignarValorCampoInterConsulta("inptFiltroRapidoEstadoInterConsulta", valor);
        asignarValorCampoInterConsulta("inptFiltroRapidoTipoInterConsulta", "");
    } else if (campo == "tipo") {
        asignarValorCampoInterConsulta("inptFiltroRapidoTipoInterConsulta", valor);
        asignarValorCampoInterConsulta("inptFiltroRapidoEstadoInterConsulta", "");
    }

    aplicarFiltrosInterConsultaDesdeBarra();
}

function actualizarChipActivoInterConsulta() {
    const estado= valorCampoInterConsulta("inptFiltroRapidoEstadoInterConsulta") || valorCampoInterConsulta("inptBuscarInterConsulta5");
    const tipo= valorCampoInterConsulta("inptFiltroRapidoTipoInterConsulta") || valorCampoInterConsulta("inptBuscarInterConsulta4");
    let activo= "todos";
    if (estado != "") {
        activo= "estado:" + estado;
    } else if (tipo != "") {
        activo= "tipo:" + tipo;
    }

    document.querySelectorAll(".interconsulta-filter-chip").forEach(function(chip) {
        chip.classList.toggle("is-active", chip.getAttribute("data-interconsulta-chip") == activo);
    });
}

function textoDetalleHilo(valor, fallback= "Sin dato") {
    const texto= (valor === null || valor === undefined) ? "" : String(valor).trim();
    return texto == "" || texto.toLowerCase() == "null" ? fallback : texto;
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

function actualizarCabeceraDetalleHilo(datosHilo, opcionesDictamen= "") {
    if (!datosHilo) {
        return;
    }

    const codigo= textoDetalleHilo(datosHilo.cod_interConsulta, "-");
    const asunto= tituloAsuntoHilo(datosHilo.asunto);
    const estado= textoDetalleHilo(datosHilo.estado, "Sin estado");
    const tipo= textoDetalleHilo(datosHilo.tipo, "Sin tipo");
    const local= textoDetalleHilo(datosHilo.nombre_local, "Sin local");
    const venta= textoDetalleHilo(datosHilo.num_factura || datosHilo.cod_ventaFK, "Sin venta asociada");
    const monto= textoDetalleHilo(datosHilo.monto_limite, "Sin monto limite");
    const pendiente= Number(datosHilo.cantMensajesNoLeidos || 0) > 0;
    const codVentaFK= textoDetalleHilo(datosHilo.cod_ventaFK, "");
    const vinculado= Number(datosHilo.cantAsociadoGastos || 0) > 0 || (codVentaFK != "" && codVentaFK != "0");
    const tieneDictamen= String(opcionesDictamen || "").trim() != "";

    document.getElementById("tituloInterConsultas").textContent= "Hilo #" + codigo + " — " + asunto;
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

    const resumen= document.getElementById("contenedorEncabezadoInterConsulta");
    if (resumen) {
        resumen.classList.toggle("is-pending", pendiente);
        resumen.classList.toggle("is-linked", vinculado);
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
    sincronizarOpcionesRapidasInterConsulta();
    actualizarChipActivoInterConsulta();
    inicializarLecturaEditorInterConsulta();
});
window.addEventListener("load", function() {
    sincronizarOpcionesRapidasInterConsulta();
    actualizarChipActivoInterConsulta();
    inicializarLecturaEditorInterConsulta();
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
    
    buscarPacientesConInterConsultas2(cod_interC, asunto, nombre_responsable, nombre_cliente, estado, tipo, cod_localFK, userid, 10, ocultar_inactivos, usuario_vinculado, busqueda_global, fecha_desde, fecha_hasta);
}

function buscarPacientesConInterConsultas2(cod_interC, asunto, nombre_responsable, nombre_cliente, estado, tipo, cod_localFK, cod_usuarioFK, limite, ocultar_inactivos, usuario_vinculado, busqueda_global= "", fecha_desde= "", fecha_hasta= "") {
    let datos= new FormData();
    datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", 'buscarInterConsultas');
    datos.append("cod_interConsulta", cod_interC);
    datos.append("cod_usuarioFK", cod_usuarioFK);
    datos.append("asunto", asunto);
    datos.append("nombre_responsable", nombre_responsable);
    datos.append("nombre_cliente", nombre_cliente);
    datos.append("estado", estado);
    datos.append("tipo", tipo);
    datos.append("usuario_vinculado", usuario_vinculado);
    datos.append("cod_localFK", cod_localFK);
    datos.append("busqueda_global", busqueda_global);
    datos.append("fecha_desde", fecha_desde);
    datos.append("fecha_hasta", fecha_hasta);

    // Evalua si se ocultan los inactivos
    if (ocultar_inactivos) {
        datos.append("ocultar_inactivos", ocultar_inactivos);
    }

    if (limite != 0) {
        busquedaInterConsultaCancelada= false;
        controldebusquedadInformeInterConsulta= false;
        registrocargadoInterConsulta= 0;
        registroInterConsultaAbierta= 0;
        document.getElementById('table_frm_VistaInterConsulta').innerHTML= paginacargando;
        datos.append("limite", limite);
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
            manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
            ver_vetana_informativa("SE HA PRODUCTIDO UN ERROR");
            controldebusquedadInformeInterConsulta= false;
		},
		success: function (responseText) {
            Respuesta = responseText;
			console.log(Respuesta)
			try {
                var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
                Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta) {
                    if (limite != 0 && busquedaInterConsultaCancelada) {
                        return;
                    }

                    // Verifica si hay mensajes pendientes en una interconsulta abierta
                    if (cod_interConsulta) {
                        datos["3"].forEach(function(valor) {
                            if (valor.cod_interConsulta == cod_interConsulta && parseInt(valor.cantMensajes) > totalRegistroMensaje) {
                                document.getElementById('avisoMensajesPendientesInterConsulta').style.display= "flex";
                            }
                        });
                    }

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
    					document.getElementById('table_frm_VistaInterConsulta').innerHTML= datos["2"];

                        registrocargadoInterConsulta= Number(datos["4"]);
                        registroInterConsultaAbierta= Number(datos["7"]);
                        totalregistroinformeInterConsulta= Number(datos["5"]);
                        if(totalregistroinformeInterConsulta>registrocargadoInterConsulta){
                            var porce=((registrocargadoInterConsulta*100)/totalregistroinformeInterConsulta).toFixed(0)
                            document.getElementById("tbProcessInformeInterConsulta").style.display=""
                            document.getElementById("divProgressInformeInterConsulta").style.width=porce+"%"
	    					document.getElementById("divProgressInformeInterConsulta").style.backgroundColor="rgb(76, 175, 80)";
    
                            controldebusquedadInformeInterConsulta=true;

                            buscarMasPacientesConInterConsultas2(cod_interC, cod_usuarioFK, asunto, nombre_responsable, nombre_cliente, estado, tipo, cod_localFK, ("10 OFFSET "+registrocargadoInterConsulta), ocultar_inactivos, usuario_vinculado, busqueda_global, fecha_desde, fecha_hasta);
                        }else{
                            controldebusquedadInformeInterConsulta=false
                        }
    
                        // Completa los contadores
                        document.getElementById('inptRegistoCargadoInterConsulta').value= registrocargadoInterConsulta;
                        document.getElementById('inptRegistoInterConsultaAbierta').value= registroInterConsultaAbierta;
                    }
				}
			} catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
                var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function buscarMasPacientesConInterConsultas2(cod_interC, cod_usuarioFK, asunto, nombre_responsable, nombre_cliente, estado, tipo, cod_localFK, limite, ocultar_inactivos, usuario_vinculado, busqueda_global= "", fecha_desde= "", fecha_hasta= "") {
    let datos= new FormData();
    datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", 'buscarInterConsultas');
    datos.append("cod_interConsulta", cod_interC);
    datos.append("cod_usuarioFK", cod_usuarioFK);
    datos.append("cod_localFK", cod_localFK);
    datos.append("asunto", asunto);
    datos.append("nombre_responsable", nombre_responsable);
    datos.append("nombre_cliente", nombre_cliente);
    datos.append("estado", estado);
    datos.append("tipo", tipo);
    datos.append("usuario_vinculado", usuario_vinculado);
    datos.append("limite", limite);
    datos.append("busqueda_global", busqueda_global);
    datos.append("fecha_desde", fecha_desde);
    datos.append("fecha_hasta", fecha_hasta);

    if (!controldebusquedadInformeInterConsulta) {
        return;
    }
    
    // Evalua si se ocultan los inactivos
    if (ocultar_inactivos) {
        datos.append("ocultar_inactivos", ocultar_inactivos);
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
            manejadordeerroresjquery(jqXHR.status,textstatus,"abmventana");
            ver_vetana_informativa("SE HA PRODUCTIDO UN ERROR");
            controldebusquedadInformeInterConsulta= false;
		},
		success: function (responseText) {
			Respuesta = responseText;
			console.log(Respuesta)
            
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
                Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta) {
                    if (busquedaInterConsultaCancelada || !controldebusquedadInformeInterConsulta) {
                        return;
                    }

                    const registrosRecibidos= Number(datos["4"]);
                    registrocargadoInterConsulta += registrosRecibidos;
                    registroInterConsultaAbierta += Number(datos["7"]);
                    document.getElementById('table_frm_VistaInterConsulta').innerHTML = document.getElementById('table_frm_VistaInterConsulta').innerHTML + datos["2"];
                    if (registrosRecibidos == 0) {
                        document.getElementById("tbProcessInformeInterConsulta").style.display="none"
                        controldebusquedadInformeInterConsulta=false
                        return;
                    }
                    if(totalregistroinformeInterConsulta>registrocargadoInterConsulta){
                        var porce=((registrocargadoInterConsulta*100)/totalregistroinformeInterConsulta).toFixed(0)
                        document.getElementById("divProgressInformeInterConsulta").style.width=porce+"%"
						document.getElementById("divProgressInformeInterConsulta").style.backgroundColor="rgb(76, 175, 80)";
                        buscarMasPacientesConInterConsultas2(cod_interC, cod_usuarioFK, asunto, nombre_responsable, nombre_cliente, estado, tipo, cod_localFK, ("10 OFFSET "+registrocargadoInterConsulta), ocultar_inactivos, usuario_vinculado, busqueda_global, fecha_desde, fecha_hasta);
                    }else{
                        document.getElementById("tbProcessInformeInterConsulta").style.display="none"
                        controldebusquedadInformeInterConsulta=false
                    }
                    // Completa los contadores
                    document.getElementById('inptRegistoCargadoInterConsulta').value= registrocargadoInterConsulta;
                    document.getElementById('inptRegistoInterConsultaAbierta').value= registroInterConsultaAbierta;
				}
			} catch (error) {
                document.getElementById("divProgressInformeInterConsulta").style.backgroundColor='#ff5722'
                controldebusquedadInformeInterConsulta= false;
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
                var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
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
    const local= document.getElementById('inptLocalAbmInterConsulta').value;
    const monto_limite= document.getElementById('inptMontoLimiteAbmInterConsulta').value.replace('.', '');
    const observacion= document.getElementById('inptObservacionAbmInterConsulta').value;

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
    if ((tipo === 'administrativo' || tipo === 'clinico' || tipo == 'judicial') && !cod_ventaFKConsulta) {
        ver_vetana_informativa("Faltan datos", "Falta seleccionar la venta", "advertencia");
        return false;
    }
    if (!local) {
        ver_vetana_informativa("Faltan datos", "Falto seleccionar el local", "advertencia");
        return false;
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
                    document.getElementById('inptBuscarInterConsulta1').value= datos["2"];
                    buscarInterConsultasYContenido(datos["2"]);
                    document.getElementById('inptBuscarInterConsulta1').value= "";
                    verCerrarVentanaInterConsulta(false);
				} else {
					// Si el servidor responde pero con un error de aplicación (ej: error en la BD)
					const mensajeError = datos["mensaje"] || "El servidor no pudo procesar la solicitud.";
					ver_vetana_informativa("Error al crear la interconsulta: " + mensajeError);
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
function verificarCamposMensaje() {
    const fecha= document.getElementById('inptFechaAbmMensaje').value;
    const contenido= document.getElementById('inptContenidoAbmMensaje').innerHTML;
    const cod_dictamenFK= document.getElementById('dictamenAbmMensaje').value;
    if (!contenido) {
        ver_vetana_informativa("Falto ingresar un contenido");
        return false;
    }

    // Deshabilita temporalmente el boton de enviar
    document.getElementById('btnEnviarContenidoAbmMensaje').disabled= true;

    abmMensaje(fecha, contenido, cod_dictamenFK);
}

function abmMensaje(fecha, contenido, cod_dictamenFK) {
    let datos= new FormData();
    datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", 'nuevo/editar mensaje');
    datos.append("fecha_creacion", fecha);
    datos.append("contenido", contenido);
    datos.append("cod_interConsulta", cod_interConsulta);
    datos.append("cod_dictamenFK", cod_dictamenFK);

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
                        subirImagenMensajeInterconsulta(datos["2"]);
                    });
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

function marcarMensajeLeido(actualizarEncabezado= true, callback= null) {
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

    document.getElementById('inptFechaAbmMensaje').value = fechaLocal.toISOString().slice(0, 16);

    document.getElementById('inptContenidoAbmMensaje').innerHTML = "";
    document.getElementById('imgfotoAnexoInterchat').style.backgroundImage = "url('/GoodVentaAsisCap/iconos/subir_imagen.png')";
    document.getElementById('imgfotoAnexoInterchat').dataset.adjuntoUrl = "";
    document.getElementById('imgfotoAnexoInterchat').dataset.adjuntoExt = "";
    document.getElementById('imgfotoAnexoInterchat').dataset.adjuntoNombre = "";
    document.getElementById('imgfotoAnexoInterchat').classList.remove("imgFotoProductoDocumento");
    fotoMensajeInterconsulta = "";
    extMensajeInterconsulta = "";

    contadorLongitudMensaje= 0;
    document.getElementById('limiteCaracteresMensajeInterconsulta').innerText= contadorLongitudMensaje;

    // Limpiar campos dictamen
    document.getElementById('dictamenAbmMensaje').value= "";
    document.getElementById('inptAsuntoDictamenInterConsulta').value= "";
}

function subirImagenMensajeInterconsulta(cod_mens) {
    if (!(fotoMensajeInterconsulta && extMensajeInterconsulta)) {
        limpiarCamposDetallesInterConsulta();
        buscarInterConsultasYContenido(cod_interConsulta);
        limpiarcamposMensaje();
        return false;
    }
    obtener_datos_user()
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", "subirImagenMensaje");
    datos.append("cod_mensaje", cod_mens);
    datos.append("foto", fotoMensajeInterconsulta);
    datos.append("ext", extMensajeInterconsulta);

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
                limpiarCamposDetallesInterConsulta()
                buscarInterConsultasYContenido(cod_interConsulta);
				if (Respuesta != "exito") {
                    throw new Error("Error producido en subirImagenMensajeIterconsulta de JavaScript.");
                }
                verCerrarEfectoCargando("");
                limpiarcamposMensaje();
			} catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
                var titulo="Error: "+error+" \r\n Consola: "+responseText
                verCerrarEfectoCargando("");
				GuardarArchivosLog(titulo)
			}
		}
	});
}

var totalRegistroMensaje= 0;
function buscarInterConsultasYContenido(codInterConsulta, elemento = null) {
    obtener_datos_user();
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", "buscarInterConsultasYContenido");
    datos.append("cod_interConsulta", codInterConsulta);
    datos.append("cod_clienteFK", cod_clienteFK);
    datos.append("nombre_usuario", document.getElementById("lblUser").textContent);
    datos.append("limite", 5);
    
    verCerrarEfectoCargando("1");
    document.getElementById('avisoMensajesPendientesInterConsulta').style.display= "none";
    
    document.getElementById('tituloInterConsultas').innerHTML= 'Cargando...';
    document.getElementById('tituloInterConsultas2').innerHTML= 'Cargando...';
    document.getElementById('listadoMencionados').innerHTML= '';
    actualizarAvatarStackDetalleHilo();
    const detallesHilo= document.querySelector("#divAbmDetallesInterConsulta .interconsulta-thread-details");
    if (detallesHilo) {
        detallesHilo.removeAttribute("open");
    }
    document.getElementById('txtUsuarioCreadorInterConsulta').innerHTML= '';
    document.getElementById('txtFechaCreadorInterConsulta').innerHTML= '';
    document.getElementById('txtEstadoInterConsulta').innerHTML= '';
    document.getElementById('txtTipoInterConsulta').innerHTML= '';
    document.getElementById('txtCodInterConsulta').innerHTML= '';
    document.getElementById('localDetalleInterConsulta').innerHTML= '';
    document.getElementById('txtCodVenta').innerHTML= '';
    document.getElementById('txtMontoLimite').innerHTML= '';
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
				Respuesta=respuestaJqueryAjax(Respuesta)
				if (Respuesta) {
                    // Se valida la nueva variable
                    cod_interConsulta= codInterConsulta;

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

                    // Carga diferida de secciones pesadas para acelerar el primer render.
                    cargarFlujoGastosInterConsulta(codInterConsulta);

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
                var titulo="Error: "+error+" \r\n Consola: "+responseText;
                console.error(error);
                verCerrarEfectoCargando("");
				GuardarArchivosLog(titulo)
			}
		}
	});
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
    document.getElementById('inptTipoAbmInterConsulta').value= $(elemento).children('#td_datos_6').html();
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
            document.getElementById('inptTipoAbmInterConsulta').value= elemento.querySelector('#td_datos_33')?.textContent.trim();
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
    // Comprueba que el cod_cliente tenga datos
    if (!cod_cliente) {
        return false;
    }

    obtener_datos_user();
    let datos= new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", 'buscarVistaAsociadoPaciente');
    datos.append("cod_cliente", cod_cliente);
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
                    if (datos["2"]) {
                        document.getElementById('divListDetallesInterconsultasAsoc').style.display= "";
                        document.getElementById('list_abm_interConsulta_asoc').innerHTML= datos["2"];
                        document.getElementById('list_detalles_interconsultas_asoc').innerHTML= datos["2"];
                    }
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

function cancelarInformeInterConsulta() {
    busquedaInterConsultaCancelada= true;
	controldebusquedadInformeInterConsulta=false
	document.getElementById("divProgressInformeInterConsulta").style.backgroundColor='#ff5722'
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
    document.getElementById('table_abm_InterConsulta').innerHTML= "";
    document.getElementById('divListDetallesInterconsultasAsoc').style.display= "none";
    document.getElementById('divObservacionDetallesInterconsultas').style.display= "none";
}

function limpiarcamposInterconsulta() {
    cod_ventaFKConsulta= "";
    cod_interConsulta= "";
    cod_clienteConsulta= "";
    
    document.getElementById('inptAsuntoAbmInterConsulta').value= "";
    document.getElementById('inptNombreClienteAbmInterConsulta').value= "";
    document.getElementById('inptEstadoAbmInterConsulta').value= "pendiente";
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
function verCerrarVentanaDetalleInterConsulta(mostrar, anterior= '') {
    if (mostrar) {
        verCerrarVentanaListadoInterConsulta(false);
        document.getElementById("divAbmDetallesInterConsulta").style.display= "";

        if (!anterior && ventanaAnterior.length > 0) {
            const ultimoElemento= ventanaAnterior[ventanaAnterior.length - 1]
            document.getElementById(ultimoElemento).style.display= 'none';
            ventanaAnterior.pop();
        }
    } else {
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
        buscarPacientesConInterConsultas();

        document.getElementById("divListadoInterConsulta").style.display= "";

        if (anterior) {
            document.getElementById(anterior).style.display= "none";
        }

        switch (anterior) {
            case 'divAbmInterConsulta':
                document.getElementById('divAbmDetallesInterConsulta').style.display= 'none';
                break;
        }
    } else {
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

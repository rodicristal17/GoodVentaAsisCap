var controldebusquedadInformeInterConsulta= true;
var totalregistroinformeInterConsulta= 0;
var registrocargadoInterConsulta= 0;
var registroInterConsultaAbierta= 0;
var cod_interConsulta= "";

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
    
    buscarPacientesConInterConsultas2(cod_interC, asunto, nombre_responsable, nombre_cliente, estado, tipo, cod_localFK, userid, 10, ocultar_inactivos, usuario_vinculado);
}

function buscarPacientesConInterConsultas2(cod_interC, asunto, nombre_responsable, nombre_cliente, estado, tipo, cod_localFK, cod_usuarioFK, limite, ocultar_inactivos, usuario_vinculado) {
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

    // Evalua si se ocultan los inactivos
    if (ocultar_inactivos) {
        datos.append("ocultar_inactivos", ocultar_inactivos);
    }

    if (limite != 0) {
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

                            buscarMasPacientesConInterConsultas2(cod_interC, cod_usuarioFK, asunto, nombre_responsable, nombre_cliente, estado, tipo, cod_localFK, ("10 OFFSET "+registrocargadoInterConsulta), ocultar_inactivos, usuario_vinculado);
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

function buscarMasPacientesConInterConsultas2(cod_interC, cod_usuarioFK, asunto, nombre_responsable, nombre_cliente, estado, tipo, cod_localFK, limite, ocultar_inactivos, usuario_vinculado) {
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
                    registrocargadoInterConsulta += Number(datos["4"]);
                    registroInterConsultaAbierta += Number(datos["7"]);
                    if(totalregistroinformeInterConsulta>registrocargadoInterConsulta){
                        var porce=((registrocargadoInterConsulta*100)/totalregistroinformeInterConsulta).toFixed(0)
                        document.getElementById("divProgressInformeInterConsulta").style.width=porce+"%"
						document.getElementById("divProgressInformeInterConsulta").style.backgroundColor="rgb(76, 175, 80)";
                        document.getElementById('table_frm_VistaInterConsulta').innerHTML = document.getElementById('table_frm_VistaInterConsulta').innerHTML + datos["2"];
                        buscarMasPacientesConInterConsultas2(cod_interC, cod_usuarioFK, asunto, nombre_responsable, nombre_cliente, estado, tipo, cod_localFK, ("10 OFFSET "+registrocargadoInterConsulta), ocultar_inactivos, usuario_vinculado);
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
    if(!confirm("¿Esta seguro que desea fusionar los hilos?")) { return;}
    
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
    monto_limite= monto_limite.replace(".", "");

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
                    subirImagenMensajeInterconsulta(datos["2"]);
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

function marcarMensajeLeido() {
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
		},
		success: function (responseText) {
			Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta != "exito") {
					ver_vetana_informativa("Error al marcar mensaje como leído.");
				} else {
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
    fotoMensajeInterconsulta = "";
    extMensajeInterconsulta = "";

    contadorLongitudMensaje= 0;
    document.getElementById('limiteCaracteresMensajeInterconsulta').innerText= contadorLongitudMensaje;
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
    document.getElementById('listadoMencionados').innerHTML= '';
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
                    document.getElementById('tituloInterConsultas2').innerHTML= datos['4']['asunto'];
                    if (datos['4']['cod_ventaFK']) {
                        document.getElementById('tituloInterConsultas2').innerHTML += ' - ' + datos['4']['nombre_persona'];
                    }
                    document.getElementById('listadoMencionados').innerHTML= datos['6'];
                    document.getElementById('txtUsuarioCreadorInterConsulta').innerHTML= datos["4"]['nombre_persona_creador'];
                    document.getElementById('txtFechaCreadorInterConsulta').innerHTML= datos["4"]['fecha_creacion'];
                    document.getElementById('txtEstadoInterConsulta').innerHTML= datos["4"]['estado'];
                    document.getElementById('txtTipoInterConsulta').innerHTML= datos["4"]['tipo'];
                    document.getElementById('txtCodInterConsulta').innerHTML= datos["4"]['cod_interConsulta'];
                    document.getElementById('localDetalleInterConsulta').innerHTML= datos["4"]['nombre_local'];
                    document.getElementById('txtCodVenta').innerHTML= datos["4"]['num_factura'];
                    document.getElementById('txtMontoLimite').innerHTML= datos["4"]['monto_limite'];

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

                    document.getElementById('txtEstadoInterConsulta').className= "badge text-uppercase "+claseEstado;
                    // Evalua si existen mensajes sin leer
                    document.getElementById('contenedorEncabezadoInterConsulta').style.border= "10px solid "+colorTarjeta;

                    // Se evalua si se recibio los elementos para el titulo y otros detalles
                    if (elemento) {
                        document.getElementById('inptNombreClienteAbmInterConsulta').value= $(elemento).children('#td_datos_2').html();
                        document.getElementById('tituloInterConsultas').innerHTML= $(elemento).children('#td_datos_10').html() + " - " + cod_interConsulta;
                    }

                    // Asigna los dictamenes al select de mensajes
                    document.getElementById('dictamenAbmMensaje').innerHTML= "<option value=''>Ninguna</option>" + datos["7"];

                    // Carga las observaciones en caso de existir
                    if (datos["4"]['observacion']) {
                        document.getElementById('divObservacionDetallesInterconsultas').style.display= "";
                        document.getElementById('divObservacionDetallesInterconsultas').innerHTML= '<span style="text-decoration: underline;"><b>Observaciones: </b></span><br>'
                        +'<span style= "font-size: 14dp;"><b>'+ datos["4"]['observacion'] + '</b></span>';
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
                        default:
                            break;
                    }
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
    document.getElementById("contenedorFlujoGastosInterConsulta").innerHTML = '<div class="text-secondary" style="padding: 8px;">Cargando gastos...</div>';

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
            contenedor.innerHTML = '<div class="text-danger" style="padding: 8px;">No se pudo cargar el flujo de gastos.</div>';
        },
        success: function (responseText) {console.log(responseText);
            try {
                const respuesta = $.parseJSON(responseText);
                if (String(cod_interConsulta) !== String(codInterConsulta)) {return;}
                if (respuesta["1"] === "exito") {
                    document.getElementById("contenedorFlujoGastosInterConsulta").innerHTML = respuesta["2"] || '<div class="text-secondary" style="padding: 8px;">Sin gastos asociados.</div>';
                } else {
                    document.getElementById("contenedorFlujoGastosInterConsulta").innerHTML = '<div class="text-danger" style="padding: 8px;">No se pudo cargar el flujo de gastos.</div>';
                }
            } catch (error) {
                if (String(cod_interConsulta) !== String(codInterConsulta)) {return;}
                document.getElementById("contenedorFlujoGastosInterConsulta").innerHTML = '<div class="text-danger" style="padding: 8px;">No se pudo cargar el flujo de gastos.</div>';
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

                    // Prepara btn para cargar mensajes anteriores y elimina el existente
                    elemContenedor.children[0].remove();
                    let btnMasMensajes= "";
                    if ((parseInt(offset) + 10) < parseInt(totalRegistroMensaje)) {
                        btnMasMensajes= "<div style='width: 100%; justify-content: center;'>"+
                                "<button class='btn btn-success' onclick='verMasMensajesInterconsulta("+(offset + 10)+", "+cod_dictamen+")'>Ver más mensajes...</button>"+
                            "</div>";
                    }
                    
                    // Agrega los mensajes anteriores
                    document.getElementById(contenedor).innerHTML= btnMasMensajes + datos["2"] + document.getElementById(contenedor).innerHTML;
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
    dropdown.style.position = 'absolute';
    dropdown.style.background = '#fff';
    dropdown.style.border = '1px solid #ccc';
    dropdown.style.zIndex = 1000;
    document.body.appendChild(dropdown);
  }
  
  // Posicionar debajo del textarea
  const rect = textarea.getBoundingClientRect();
  dropdown.style.left = rect.left + 'px';
  dropdown.style.top = rect.bottom + 'px';
  dropdown.style.width = rect.width + 'px';
  
  // Rellenar con sugerencias
  dropdown.innerHTML = '';
  sugerencias.forEach(([id_sug, nombre]) => {
    const item = document.createElement('div');
    item.textContent = nombre;
    item.id= id_sug;
    item.className = 'menciones-mensaje';
    item.onclick = () => {
      // Reemplazar el @texto por el nombre completo
      textarea.innerHTML = textarea.innerHTML.replace(
        /@\S*$/, 
        '<b class="menciones-mensaje" id="'+id_sug+'">@' + nombre + '</b>&nbsp;'
      );
      dropdown.innerHTML = '';
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

var cod_dictamenSeleccionado= "";
function verCerrarVentanaSeleccionDictamen(mostrar) {
    const ventanaSeleccion= document.getElementById('divSeleccionDictamenInterConsulta');

    if (!ventanaSeleccion) {
        return;
    }

    if (mostrar) {
        document.getElementById('divAbmDetallesInterConsulta').style.display= "none";
        ventanaSeleccion.style.display= "";
    } else {
        ventanaSeleccion.style.display= "none";
        document.getElementById('divAbmDetallesInterConsulta').style.display= "";
    }
}

function verificarCamposDictamen() {
    const resultado= document.getElementById('inptContenidoAbmMensaje').innerHTML.trim();

    if (!cod_interConsulta) {
        ver_vetana_informativa("Faltan datos", "No se encontró la interconsulta para registrar el dictamen.", "advertencia");
        return false;
    }
    if (!resultado) {
        ver_vetana_informativa("Falto ingresar un contenido");
        return false;
    }

    const asunto_dictamen= document.getElementById("inptAsuntoAbmInterConsulta").value;
    document.getElementById('btnDictamenContenidoAbmMensaje').disabled= true;
    abmDictamen(resultado, asunto_dictamen, 'solicitado');
}

function abmDictamen(resultado, asunto, estado= 'solicitado') {
    let datos= new FormData();
    datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", 'nuevo/editar dictamen');
    datos.append("id", cod_dictamenSeleccionado);
    datos.append("resultado", resultado);
    datos.append("asunto", asunto);
    datos.append("estado", estado);
    datos.append("cod_interConsultaFK", cod_interConsulta);

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
            document.getElementById('btnDictamenContenidoAbmMensaje').disabled= false;
		},
		success: function (responseText) {
			Respuesta = responseText;
			console.log(Respuesta)
            document.getElementById('btnDictamenContenidoAbmMensaje').disabled= false;
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta == "exito") {
                    cod_dictamenSeleccionado= datos["2"];
                    ver_vetana_informativa("Dictamen guardado.", "", "info");
                    buscarListadoInterConsultas();
                    verCerrarVentanaSeleccionDictamen(true);
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

function asignarMensajesDictamen() {
    const div= document.getElementById('divMensajesSelectDictamenInterConsulta');
    const mensajesSeleccionados= [];

    if (!cod_dictamenSeleccionado) {
        ver_vetana_informativa("Faltan datos", "Primero debe registrarse el dictamen.", "advertencia");
        return false;
    }

    // Obtiene un array solo de los mensajes seleccionados
    div.querySelectorAll('table').forEach(function(tabla) {
        tabla.querySelectorAll('tr').forEach(function(fila) {
            const checkbox= fila.querySelector('#td_datos_1 input[type="checkbox"]');
            const tdId= fila.querySelector('#td_id');

            if (checkbox && checkbox.checked && tdId) {
                mensajesSeleccionados.push(tdId.textContent.trim());
            }
        });
    });

    if (mensajesSeleccionados.length === 0) {
        ver_vetana_informativa("Faltan datos", "Seleccione al menos un mensaje para asignar al dictamen.", "advertencia");
        return false;
    }

    const asunto_dictamen= document.getElementById('inptAsuntoDictamenInterConsulta').value;
    if (!asunto_dictamen) {
        ver_vetana_informativa("Faltan datos", "El asunto del dictamen se asignará a los mensajes seleccionados. Si desea mantener el asunto actual de los mensajes, deje el campo de asunto vacío.", "advertencia");
        return false;
    }
    
    obtener_datos_user();
    var datos = new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "asignarMensajesDictamen");
    datos.append("asunto", asunto_dictamen);
    datos.append("cod_mensajeFK", mensajesSeleccionados.join(";"));
    datos.append("cod_dictamen", cod_dictamenSeleccionado);

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
                    ver_vetana_informativa("Mensajes asignados.", "", "info");
                    cod_dictamenSeleccionado= "";
                    document.getElementById('divMensajesSelectDictamenInterConsulta').innerHTML= "";
                    verCerrarVentanaSeleccionDictamen(false);
                    buscarInterConsultasYContenido(cod_interConsulta);
                    limpiarcamposMensaje();
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
            ventanaAnterior.pop();
            document.getElementById('divAbmGastos').style.display= "none";
            verCerrarVentanaDetalleInterConsulta(true, 'divAbmGastos');
            buscarInterConsultasYContenido(cod_interConsulta);
            break;
        case 'divAbmDetallesInterConsulta':
            cod_ventaFKConsulta= $(elemento).children('#td_datos_4').html();
            cod_clienteConsulta= $(elemento).children('#td_datos_7').html();
            buscarInterConsultasYContenido($(elemento).children('#td_id').html(), elemento);
            break;
        case 'divListadoInterConsulta':
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
                    document.getElementById('divListDetallesInterconsultasAsoc').style.display= "";
                    document.getElementById('list_abm_interConsulta_asoc').innerHTML= datos["2"];
                    document.getElementById('list_detalles_interconsultas_asoc').innerHTML= datos["2"];
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
	controldebusquedadInformeInterConsulta=false
	document.getElementById("divProgressInformeInterConsulta").style.backgroundColor='#ff5722'
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

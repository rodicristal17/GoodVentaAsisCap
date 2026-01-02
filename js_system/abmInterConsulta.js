var controldebusquedadInformeInterConsulta= true;
var totalregistroinformeInterConsulta= 0;
var registrocargadoInterConsulta= 0;
var registroInterConsultaAbierta= 0;
var cod_interConsulta= "";

function buscarPacientesConInterConsultas() {
    const cod_interConsulta= document.getElementById('inptBuscarInterConsulta1').value;
    const asunto= document.getElementById('inptBuscarInterConsulta2').value;
    const nombre_responsable= document.getElementById('inptBuscarInterConsulta6').value;
    const nombre_cliente= document.getElementById('inptBuscarInterConsulta3').value;
    const estado= document.getElementById('inptBuscarInterConsulta5').value;
    const tipo= document.getElementById('inptBuscarInterConsulta4').value;
    const ocultar_inactivos= document.getElementById('inptSeleccFiltroEstadoInterConsulta').checked;
    
    buscarPacientesConInterConsultas2(cod_interConsulta, asunto, nombre_responsable, nombre_cliente, estado, tipo, 10, ocultar_inactivos);
}

function buscarPacientesConInterConsultas2(cod_interConsulta, asunto, nombre_responsable, nombre_cliente, estado, tipo, limite, ocultar_inactivos) {
    let datos= new FormData();
    datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", 'buscarInterConsultas');
    datos.append("cod_interConsulta", cod_interConsulta);
    datos.append("cod_usuarioFK", userid);
    datos.append("asunto", asunto);
    datos.append("nombre_responsable", nombre_responsable);
    datos.append("nombre_cliente", nombre_cliente);
    datos.append("estado", estado);
    datos.append("tipo", tipo);
    datos.append("limite", limite);

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
					document.getElementById('table_frm_VistaInterConsulta').innerHTML= datos["2"];

                    // Una busqueda unica
                    if (limite == 0) {
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

                    registrocargadoInterConsulta= Number(datos["4"]);
                    registroInterConsultaAbierta= Number(datos["7"]);
                    totalregistroinformeInterConsulta= Number(datos["5"]);
                    if(totalregistroproductos>registrocargadoproductos){
                        var porce=((registrocargadoproductos*100)/totalregistroproductos).toFixed(0)
                        document.getElementById("divProgressInformeInterConsulta").style.width=porce+"%"
                        buscarMasPacientesConInterConsultas2(cod_interConsulta, asunto, nombre_responsable, nombre_cliente, estado, tipo, "10 OFFSET "+registrocargadoInterConsulta, ocultar_inactivos);
                    }else{
                        controldebusquedadInformeInterConsulta=false
                    }

                    // Completa los contadores
                    document.getElementById('inptRegistoCargadoInterConsulta').value= registrocargadoInterConsulta;
                    document.getElementById('inptRegistoInterConsultaAbierta').value= registroInterConsultaAbierta;
				}
			} catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
                var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function buscarMasPacientesConInterConsultas2(cod_interConsulta, asunto, nombre_responsable, nombre_cliente, estado, tipo, limite, ocultar_inactivos) {
    let datos= new FormData();
    datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", 'buscarInterConsultas');
    datos.append("cod_interConsulta", cod_interConsulta);
    datos.append("cod_usuarioFK", userid);
    datos.append("asunto", asunto);
    datos.append("nombre_responsable", nombre_responsable);
    datos.append("nombre_cliente", nombre_cliente);
    datos.append("estado", estado);
    datos.append("tipo", tipo);
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
					document.getElementById('table_frm_VistaInterConsulta').innerHTML= datos["2"];
                    if (datos["6"] > 0) {
                        document.getElementById('avisoMensajesPendientes').style.display= "";
                    } else {
                        document.getElementById('avisoMensajesPendientes').style.display= "none";
                    }

                    registrocargadoInterConsulta += Number(datos["4"]);
                    registroInterConsultaAbierta += Number(datos["7"]);
                    if(totalregistroproductos>registrocargadoproductos){
                        var porce=((registrocargadoproductos*100)/totalregistroproductos).toFixed(0)
                        document.getElementById("divProgressInformeInterConsulta").style.width=porce+"%"
					    document.getElementById('table_frm_VistaInterConsulta').innerHTML = document.getElementById('table_frm_VistaInterConsulta').innerHTML . datos["2"];
                        buscarMasPacientesConInterConsultas2(cod_interConsulta, asunto, nombre_responsable, nombre_cliente, estado, tipo, "10 OFFSET "+registrocargadoInterConsulta, ocultar_inactivos);
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

function verificarCamposInterConsulta() {
    const asunto= document.getElementById('inptAsuntoAbmInterConsulta').value;
    const estado= document.getElementById('inptEstadoAbmInterConsulta').value;
    const tipo= document.getElementById('inptTipoAbmInterConsulta').value;

    if (!asunto) {
        ver_vetana_informativa("El campo asunto es obligatorio para crear una nueva Interconsulta.");
        return false;
    }
    if ((tipo === 'administrativo' || tipo === 'clinico') && !cod_ventaFKConsulta) {
        ver_vetana_informativa("Falta seleccionar la venta");
        return false;
    }
    
    abmInterConsulta(asunto, estado, tipo);
}

function abmInterConsulta(asunto, estado, tipo) {
    let datos= new FormData();
    datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", 'nuevo/editar interconsulta');
    datos.append("estado", estado);
    datos.append("asunto", asunto);
    datos.append("tipo", tipo);
    datos.append("cod_interConsulta", cod_interConsulta);
    datos.append("cod_ventaFK", cod_ventaFKConsulta);

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
					ver_vetana_informativa("Datos guardados exitosamente.");

                    buscarInterConsultasYContenido();
                    verCerrarVentanaInterConsulta(false, 'abm');
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
					ver_vetana_informativa("Datos guardados exitosamente.");

                    
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
function verificarCamposMensaje(codInterconsulta) {
    const fecha= document.getElementById('inptFechaAbmMensaje'+codInterconsulta).value;
    const contenido= document.getElementById('inptContenidoAbmMensaje'+codInterconsulta).innerHTML;
    if (!contenido) {
        ver_vetana_informativa("Falto ingresar un contenido");
        return false;
    }

    // Deshabilita temporalmente el boton de enviar
    document.getElementById('btnEnviarContenidoAbmMensaje'+codInterconsulta).disabled= true;

    abmMensaje(codInterconsulta, fecha, contenido);
}

function abmMensaje(codInterconsulta,fecha, contenido) {
    let datos= new FormData();
    datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", 'nuevo/editar mensaje');
    datos.append("fecha_creacion", fecha);
    datos.append("contenido", contenido);
    datos.append("cod_interConsulta", codInterconsulta);

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
            document.getElementById('btnEnviarContenidoAbmMensaje'+codInterconsulta).disabled= false;
		},
		success: function (responseText) {
			Respuesta = responseText;
			console.log(Respuesta)
            document.getElementById('btnEnviarContenidoAbmMensaje'+codInterconsulta).disabled= false;
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta == "exito") {
					ver_vetana_informativa("Datos guardados exitosamente.");
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

function marcarMensajeLeido(elemento) {
    const cod_interC= elemento.id.substr(23);

    let datos= new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", 'marcarMensajesLeido');
    datos.append("cod_interConsulta", cod_interC);
    
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
                    let color= document.getElementById("contenedorEncabezadoInterConsulta"+cod_interC).style.border;
                    color= color.substring(11);
                    document.getElementById("contenedorEncabezadoInterConsulta"+cod_interC).style.border= "none";
                    document.getElementById("contenedorEncabezadoInterConsulta"+cod_interC).style.borderLeft= "10px solid "+color;
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

function limpiarcamposMensaje(codInterconsulta) {
    const fechaActual= new Date();
    document.getElementById('inptFechaAbmMensaje'+codInterconsulta).value= fechaActual.toISOString().slice(0,16);
    document.getElementById('inptContenidoAbmMensaje'+codInterconsulta).innerHTML= "";
    fotoMensajeInterconsulta= "";
    extMensajeInterconsulta= "";
}

function subirImagenMensajeInterconsulta(cod_mens) {
    if (!(fotoMensajeInterconsulta && extMensajeInterconsulta)) {
        buscarInterConsultasYContenido();
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
                buscarInterConsultasYContenido();
				if (Respuesta != "exito") {
                    throw new Error("Error producido en subirImagenMensajeIterconsulta de JavaScript.");
                }
                verCerrarEfectoCargando("");
			} catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
                var titulo="Error: "+error+" \r\n Consola: "+responseText
                verCerrarEfectoCargando("");
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function buscarInterConsultasYContenido() {
    verCerrarEfectoCargando("1");
    // Obtiene tambien el listado de usuario
    buscarabmusuario2('', '', '', 'Activo', '');

    obtener_datos_user()
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", "buscarInterConsultasYContenido");
    datos.append("cod_interConsulta", cod_interConsulta);
    datos.append("cod_clienteFK", cod_clienteFK);
    datos.append("nombre_usuario", document.getElementById("lblUser").textContent);
    datos.append("limite", 5);

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
				} else {
                    throw new Error("Error producido en buscarInterConsultas de JavaScript.");
                }

                // Limpia campos
                fotoMensajeInterconsulta= "";
                extMensajeInterconsulta= "";

                verCerrarEfectoCargando("");
			} catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
                var titulo="Error: "+error+" \r\n Consola: "+responseText
                verCerrarEfectoCargando("");
				GuardarArchivosLog(titulo)
			}
		}
	});
}

function verMasMensajesInterconsulta(cod_interCon, offset) {
    obtener_datos_user()
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
    datos.append("accion", "buscarMasInterConsultasYContenido");
    datos.append("cod_interConsulta", cod_interCon);
    datos.append("limite", 10);
    datos.append("offset", offset);

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
                    const contenedor= "contenedorMensajesInterConsulta"+cod_interCon;
                    // Elimita el boton de ver mas
                    const elemContenedor = document.getElementById(contenedor);
                    elemContenedor.children[0].remove();
                    
                    // Asigna los mensajes anteriores
                    document.getElementById(contenedor).innerHTML= datos["2"] + document.getElementById(contenedor).innerHTML;
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

    var OpAjax = $.ajax({
		data: datos,
		url: "../php_system/abmInventarioLocal.php",
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

function obtenerDatosInterConsulta(elemento) {
    cod_interConsulta= $(elemento).children('#td_id').html();
    cod_ventaFKConsulta= $(elemento).children('#td_datos_7').html();
    document.getElementById('inptNombreClienteAbmInterConsulta').value= $(elemento).children('#td_datos_2').html();
    document.getElementById('tituloInterConsultas').innerHTML= cod_ventaFKConsulta ? "InterConsultas - "+$(elemento).children('#td_datos_5').html()+" - "+cod_clienteFK : "InterConsultas";

    verCerrarVentanaInterConsulta(true, 'detalle');
    buscarInterConsultasYContenido();
}

function obtenerDetallesInterConsulta(elemento) {
    cod_interConsulta= elemento.querySelector('#td_datos_4')?.textContent.trim();
    document.getElementById('inptAsuntoAbmInterConsulta').value= elemento.querySelector('#td_datos_1')?.textContent.trim();
    document.getElementById('inptTipoAbmInterConsulta').value= elemento.querySelector('#td_datos_3')?.textContent.trim();
    document.getElementById('inptEstadoAbmInterConsulta').value= elemento.querySelector('#td_datos_2')?.textContent.trim();
    cod_ventaFKConsulta= elemento.querySelector('#td_datos_5')?.textContent.trim();
    document.getElementById('inptNombreClienteAbmInterConsulta').value= elemento.querySelector('#td_datos_7')?.textContent.trim();

    verCerrarVentanaInterConsulta(true, 'abm', 'divAbmInterConsulta2');
}

function cancelarInformeInterConsulta() {
	controldebusquedadInformeInterConsulta=false
	document.getElementById("divProgressInformeInterConsulta").style.backgroundColor='#ff5722'
}

function limpiarcamposInterconsulta() {
    cod_ventaFKConsulta= "";
    cod_interConsulta= "";
    
    document.getElementById('inptAsuntoAbmInterConsulta').value= "";
    document.getElementById('inptNombreClienteAbmInterConsulta').value= "";
    document.getElementById('inptEstadoAbmInterConsulta').value= "pendiente";
}

var ventanaAnterior= '';
function verCerrarVentanaInterConsulta(mostrar, ventana, anterior= '') {
    if (mostrar) {
        // Comprueba si estaba minimizado
        if (document.getElementById('divMinimizadoInterConsulta').style.display == "") {
            document.getElementById('divMinimizadoInterConsulta').style.display="none";
        }

        // Oculta todo
        document.getElementById("divAbmInterConsulta1").style.display="none";
        document.getElementById("divAbmInterConsulta2").style.display="none";
        document.getElementById("divAbmInterConsulta3").style.display= "none";

        switch (ventana) {
            case 'detalle':
                document.getElementById("divAbmInterConsulta2").style.display= "";
                break;
            case 'listado':
                document.getElementById("divAbmInterConsulta").style.display="";
                buscarPacientesConInterConsultas();
                document.getElementById("divAbmInterConsulta1").style.display= "";
                break;
            case 'abm':
                document.getElementById("divAbmInterConsulta3").style.display= "";
                break;
        }

        if (ventanaAnterior) {
            document.getElementById(ventanaAnterior).style.display= 'none';
            ventanaAnterior= '';
        }
    } else {
        switch (ventana) {
            case 'detalle':
                buscarPacientesConInterConsultas();
                document.getElementById("divAbmInterConsulta1").style.display="";
                document.getElementById("divAbmInterConsulta2").style.display= "none";
                break;
            case 'listado':
			    buscarPacientesConInterConsultas2("", "", "", "", "", "", 0, true);
                document.getElementById("divAbmInterConsulta").style.display="none";
                document.getElementById("divAbmInterConsulta1").style.display= "none";
                break
            case 'abm':
                buscarPacientesConInterConsultas();
                document.getElementById("divAbmInterConsulta3").style.display= "none";
                break;
        }
        if (ventanaAnterior) {
            document.getElementById(ventanaAnterior).style.display= '';
            ventanaAnterior= '';
        }
    }
    // Guarda la ventanaAnterior
    if (anterior) {
        ventanaAnterior= anterior;
    }
}

function minimizarabmInterConsulta() {
    document.getElementById('divAbmInterConsulta').style.display="none";
    document.getElementById('divMinimizadoInterConsulta').style.display="";
}
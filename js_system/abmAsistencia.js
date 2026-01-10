var cod_asistencia= "";
function registrarAsistencia() {
    // Obtiene la hora actual
    const fechaActual = new Date();
    const hora= fechaActual.toLocaleTimeString('es-PY', { hour12: false });
	
	// Deshabilita temporalmente el boton para marcar asistencia
	document.getElementById("btnRegistrarAsistencia").disabled = true;

    obtener_datos_user()
	var datos = new FormData();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	
    if (cod_asistencia === "") {
        // Registrar entrada
        datos.append("accion", "nuevo");
    } else {
        // Registrar salida
        datos.append("accion", "registrarSalida");
		datos.append("cod_local", cod_localFKUSer);
        datos.append("cod_asistencia", cod_asistencia);
    }

	verCerrarEfectoCargando("1")
    var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmAsistencia.php",
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
            ver_vetana_informativa("Error de conexion al registrar asistencia");
			document.getElementById("btnRegistrarAsistencia").disabled = false;
		},
		success: function (responseText) {
			Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta == "exito") {
					location.reload();
                    //obtenerAsistenciaUsuario();
				} else {
					let mensaje= datos["2"];
					mensaje += (datos["3"] !== undefined) ? "<br><br>"+datos["3"] : "";
					ver_vetana_informativa(mensaje);
					if (Respuesta == 'red') {
						obtenerAsistenciaUsuario();
					}
				}
			} catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ")
                var titulo="Error: "+error+" \r\n Consola: "+responseText
				GuardarArchivosLog(titulo)
			} finally {
                verCerrarEfectoCargando("");
				document.getElementById("btnRegistrarAsistencia").disabled = false;
            }
		}
	});
}

var registrocargadoinformeAsistencia=0;
var totalregistroinformeAsistencia=0;
var controldebusquedadInformeAsistencia= true;
function obtenerVistaInformeAsistencia() {
	if(controlacceso("VERLISTADOASISTENCIA","accion")==false){ return;}

	// Obtiene los datos de filtros
	let fecha_desde= document.getElementById("inptBuscarInformeAsistenciaF1").value;
	let fecha_hasta= document.getElementById("inptBuscaInformeAsistenciaF2").value;
	const usuario= document.getElementById("inptInformeAsistencia2").value;
	const local= document.getElementById('inptLocalInformeAsistencia').value;
	const fecha= document.getElementById('inptInformeAsistencia3').value;
	const cod_asistencia= document.getElementById('inptInformeAsistencia1').value;

	// Prioriza la fecha individual de la tabla
	if (fecha != "") {
		fecha_desde = fecha;
		fecha_hasta = fecha;
	}

	obtener_datos_user()
	var datos = new FormData();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("accion", "buscarVistaInforme");
	datos.append("fecha_desde", fecha_desde);
	datos.append("fecha_hasta", fecha_hasta);
	datos.append("nombre_usuario", usuario);
	datos.append("cod_local", local);
	datos.append("cod_asistencia", cod_asistencia);
	datos.append("limite", 10);

	verCerrarEfectoCargando("1")
    var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmAsistencia.php",
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
			document.getElementById("table_InformeAsistencia").innerHTML= '';
			Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta == "exito") {
					document.getElementById("table_InformeAsistencia").innerHTML= datos["2"];
					document.getElementById("inptTotalRegistoInformeAsistencia").value= datos["5"];
					document.getElementById("inptTotalMinutosInformeAsistencia").value= datos["6"];
					totalregistroinformeAsistencia= parseInt(datos["5"]);
					registrocargadoinformeAsistencia= parseInt(datos["4"]);

					// Controla el progreso de la busqueda
					if(totalregistroinformeAsistencia>registrocargadoinformeAsistencia){
						controldebusquedadInformeAsistencia=true;
						var porce=((registrocargadoinformeAsistencia*100)/totalregistroinformeAsistencia).toFixed(0)
						document.getElementById('tbProcessInformeAsistencia').style.display= ""
						document.getElementById("divProgressInformeAsistencia").style.width=porce+"%"
						//document.getElementById("table_InformeAsistencia").innerHTML += "<div id='table_mas_InformeAsistencia'></div>"
						obtenermasVistaInformeAsistencia();
					 }else{
						document.getElementById('tbProcessInformeAsistencia').style.display= "none";
						controldebusquedadInformeAsistencia=false
					 }
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

function obtenermasVistaInformeAsistencia() {
	// Obtiene los datos de filtros
	let fecha_desde= document.getElementById("inptBuscarInformeAsistenciaF1").value;
	let fecha_hasta= document.getElementById("inptBuscaInformeAsistenciaF2").value;
	const usuario= document.getElementById("inptInformeAsistencia2").value;
	const local= document.getElementById('inptLocalInformeAsistencia').value;
	const fecha= document.getElementById('inptInformeAsistencia3').value;
	const cod_asistencia= document.getElementById('inptInformeAsistencia1').value;

	// Prioriza la fecha individual de la tabla
	if (fecha != "") {
		fecha_desde = fecha;
		fecha_hasta = fecha;
	}

	obtener_datos_user()
	var datos = new FormData();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("accion", "buscarMasVistaInforme");
	datos.append("fecha_desde", fecha_desde);
	datos.append("fecha_hasta", fecha_hasta);
	datos.append("nombre_usuario", usuario);
	datos.append("cod_local", local);
	datos.append("cod_asistencia", cod_asistencia);
	datos.append("limite", "10 OFFSET "+registrocargadoinformeAsistencia);

	verCerrarEfectoCargando("1")
    var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmAsistencia.php",
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
					document.getElementById("table_InformeAsistencia").innerHTML += datos["2"];
					let totalMinutos= parseFloat(document.getElementById("inptTotalMinutosInformeAsistencia").value);
					document.getElementById("inptTotalMinutosInformeAsistencia").value= parseFloat(datos["6"]) + totalMinutos;
					registrocargadoinformeAsistencia += parseInt(datos["4"]);

					// Controla el progreso de la busqueda
					if(totalregistroinformeAsistencia>registrocargadoinformeAsistencia){
						var porce=((registrocargadoinformeAsistencia*100)/totalregistroinformeAsistencia).toFixed(0)
						document.getElementById("divProgressInformeAsistencia").style.width=porce+"%"
						//document.getElementById("table_InformeAsistencia").innerHTML += "<div id='table_mas_InformeAsistencia' style='width: 100%;'></div>"
						obtenermasVistaInformeAsistencia();
					 }else{
						controldebusquedadInformeAsistencia=false;
						document.getElementById("divProgressInformeAsistencia").style.display="none"
						document.getElementById('tbProcessInformeAsistencia').style.display= "none";
					 }
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

function obtenerAsistenciaUsuario() {
	let fechaActual= new Date();
	
	obtener_datos_user()
	let datos = new FormData();
	datos.append("useru", userid)
	datos.append("passu", passuser)
	datos.append("navegador", navegador)
	datos.append("accion", "buscar");
	datos.append("cod_usuario", userid);
	fechaActual = fechaActual.toISOString().split('T')[0];
	datos.append("fecha_desde", fechaActual);
	datos.append("fecha_hasta", fechaActual);
	datos.append("sinSalida", true)
	datos.append("limite", 1);

	verCerrarEfectoCargando("1")
    var OpAjax = $.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmAsistencia.php",
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
            ver_vetana_informativa("Error de conexion al registrar asistencia");
			document.getElementById("btnRegistrarAsistencia").disabled = false;
		},
		success: function (responseText) {
			Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta == "exito") {
					let registros= datos["registros"];
					tablaRegistros= document.getElementById("tableRegistroEntrada");
					document.getElementById("btnRegistrarAsistencia").disabled = false;
					if (registros.length > 0) {
						cod_asistencia= registros[0]['cod_asistencia'];
						document.getElementById("btnRegistrarAsistencia").value = "Marcar Salida";
						tablaRegistros.style.display= '';
						let fila= $(tablaRegistros).children('tbody');
						fila= $(fila).children('tr')[0];
						fila.innerHTML = "<td>"+registros[0]['fecha'].substring(0, 10)+"</td>"
      						+"<td>"+registros[0]['hora_entrada']+"</td>";
					} else {
						cod_asistencia= "";
						tablaRegistros.style.display= 'none';
						document.getElementById("btnRegistrarAsistencia").value = "Marcar Entrada";
					}
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

function verCerrarInformeAsistencia(mostrar) {
	if(controlacceso("VERLISTADOASISTENCIA","accion")==false){ return;}
	if (mostrar) {
		document.getElementById("divInformeAsistencia").style.display = "";
	} else {
		document.getElementById("divInformeAsistencia").style.display = "none";
	}
}

function minimizarInformeAsistencia() {
	document.getElementById('divMinimizadoInformeAsistencia').style.display = '';
	document.getElementById("divInformeAsistencia").style.display = "none";
}

function cancelarInformeAsistencia(){
	controldebusquedadInformeAsistencia=false
	document.getElementById("divProgressInformeAsistencia").style.backgroundColor='#ff5722'
}
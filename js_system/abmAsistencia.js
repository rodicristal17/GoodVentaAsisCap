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
        datos.append("hora_entrada", hora);
    } else {
        // Registrar salida
        datos.append("accion", "registrarSalida");
        datos.append("hora_salida", hora);
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
		},
		success: function (responseText) {
			Respuesta = responseText;
			console.log(Respuesta)
			try {
				var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
				if (Respuesta == "exito") {
                    obtenerAsistenciaUsuario();
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
						cod_asistencia= registros[0][0];
						document.getElementById("btnRegistrarAsistencia").value = "Marcar Salida";
						tablaRegistros.style.display= '';
						let fila= $(tablaRegistros).children('tbody');
						fila= $(fila).children('tr')[0];
						fila.innerHTML = "<td>"+registros[0][2].substring(0, 10)+"</td>"
      						+"<td>"+registros[0][3]+"</td>";
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
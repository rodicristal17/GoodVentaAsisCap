var cod_trabajo_mecanico_dental = "";
var filtro_fecha_trabajo_mecanico_dental= 1;

function verCerrarVentanaTrabajoMecanicoDental(mostrar, mostrarAbm) {
    if(controlacceso("VERLISTADOTRABAJOMECANICODENTAL","accion")==false){ return;}
    document.getElementById('divMinimizadoTrabajoMecanicoDental').style.display= 'none';
    if (mostrar) {
        $("div[id=divAbmTrabajoMecanicoDental]").fadeIn(250);
        if (mostrarAbm) {
            buscarTiposTrabajo();
            $("div[id=divAbmTrabajoMecanicoDental1]").fadeIn(250);
            // Oculta el listado
            $("div[id=divAbmTrabajoMecanicoDental2]").fadeOut(250);
        } else {
            buscarTrabajoMecanicosDentales();
            buscarOpcionesMecanicoDental();
            $("div[id=divAbmTrabajoMecanicoDental2]").fadeIn(250);
        }
    } else {
        if (mostrarAbm) {
            $("div[id=divAbmTrabajoMecanicoDental1]").fadeOut(250);
            // Muestra el listado de vuelta
            $("div[id=divAbmTrabajoMecanicoDental2]").fadeIn(250);
        } else {
            $("div[id=divAbmTrabajoMecanicoDental2]").fadeOut(250);
            $("div[id=divAbmTrabajoMecanicoDental]").fadeOut(250);
        }
    }
}

function verificarCamposTrabajoMecanicoDental() {
    let inptTipoTrabajoMecanicoDental= '';
    $("input[id=inptTipoTrabajoMecanicoDental]").each(function (i, Elemento) {
      var $input = $(this),
          val = $input.val();
		 
          list = $input.attr('list'),
          match = $('#'+list + ' option').filter(function() {
              return ($(this).val() === val);			 
          });

       if(match.length > 0) {
         inptTipoTrabajoMecanicoDental=$(match).attr("id")
       } else {
           // value is not in list
       }
    });

    const observacion= $("#inptObservacionTrabajoMecanicoDental").val()
    const colorimetro= $("#inptColorimetriaTrabajoMecanicoDental").val()
    const costo= $("#inptCostoTrabajoMecanicoDental").val()
    const fecha_entrega= $("#inptFechaEntregaTrabajoMecanicoDental").val()
    const fecha_retiro= $("#inptFechaRetiroTrabajoMecanicoDental").val()
    const estado= $("#inptEstadoTrabajoMecanicoDental").val();
    const especialista= $("#inptDoctorTrabajoMecanicoDental").val();
    const mecanico_dental= $("#inptMecanicoTrabajoMecanicoDental").val();

    if (!inptTipoTrabajoMecanicoDental || inptTipoTrabajoMecanicoDental == "") {
        ver_vetana_informativa("Falta seleccionar el tipo de trabajo");
        return false;
    }
    if (!estado || estado == "") {
        ver_vetana_informativa("Falta seleccionar el estado");
        return false;
    }
    if (idFkVenta == "" || idFkVenta == null) {
        ver_vetana_informativa("Falta seleccionar la venta");
        return false;
    }
    if (especialista == "") {
        ver_vetana_informativa("Falta seleccionar el especialista");
        return false;
    }

    abmTrabajoMecanicoDental(inptTipoTrabajoMecanicoDental,observacion,colorimetro,costo,fecha_entrega,fecha_retiro,estado,especialista, mecanico_dental)
}

function abmTrabajoMecanicoDental(cod_tipo_trabajoFK,observacion,colorimetro,costo,fecha_entrega,fecha_retiro,estado,especialista,cod_mecanicoDentalFK) {
    obtener_datos_user();
    var datos = new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);

    // Recopilar datos del formulario
    datos.append("cod_ventaFK", idFkVenta);
    datos.append("cod_tipo_trabajoFK", cod_tipo_trabajoFK);
    datos.append("observacion", observacion);
    datos.append("colorimetro", colorimetro);
    datos.append("estado", estado);
    datos.append("costo", costo);
    datos.append("cod_especialistaFK", especialista);
    datos.append("cod_mecanicoDentalFK", cod_mecanicoDentalFK);
    datos.append("fecha_entrega", fecha_entrega);
    datos.append("fecha_retiro", fecha_retiro);
    datos.append("cod_ventaFK", idFkVenta);
    if (cod_trabajo_mecanico_dental == "") {
        datos.append("accion", "nuevo");
    } else {
        datos.append("accion", "editar");
        datos.append('cod_trabajo_mecanico_dental', cod_trabajo_mecanico_dental);
    }

    verCerrarEfectoCargando("1");
    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmTrabajoMecanicoDental.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        xhr: function () {
            var xhr = new window.XMLHttpRequest();
            //Uload progress
            xhr.upload.addEventListener("progress", function (evt) {
                var kb = ((evt.loaded * 1) / 1000).toFixed(1);
                if (kb == "0.0") { kb = 0.1; }
            }, false);
            //Download progress
            xhr.addEventListener("progress", function (evt) {
                var kb = ((evt.loaded * 1) / 1000).toFixed(1);
                if (kb == "0.0") { kb = 0.1; }
            }, false);
            return xhr;
        },
        error: function (jqXHR, textstatus, errorThrowm) {
            verCerrarEfectoCargando("");
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
            ver_vetana_informativa("Error de conexión al procesar la solicitud.");
        },
        success: function (responseText) {
            Respuesta = responseText;
            console.log(Respuesta);
            try {
                var datos = $.parseJSON(Respuesta);
                if (datos["1"] === "exito") {
                    cod_trabajo_mecanico_dental= datos["cod_trabajo_mecanico_dental"];
                    cod_mecanico_dental= datos["cod_mecanico_dental"];
                    ver_vetana_informativa("Datos guardados exitosamente.");
                } else if (datos["1"] === "UI") {
                    ver_vetana_informativa("Usuario o contraseña incorrectos.");
                } else {
                    ver_vetana_informativa("Ha ocurrido un error: " + (datos["mensaje"] || ""));
                }
            } catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR");
                var titulo = "Error: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
            } finally {
                verCerrarEfectoCargando("");
            }
        }
    });
}

function checkFechaTrabajoMecanicoDental(opcion) {
    filtro_fecha_trabajo_mecanico_dental= opcion;

    // Deselecciona las opciones
    document.getElementById('inptSeleccFiltroFechaTodosTrabajoMecanicoDental').checked= false;
    document.getElementById('inptSeleccFiltroFechaEntregaTrabajoMecanicoDental').checked= false;
    document.getElementById('inptSeleccFiltroFechaRetiroTrabajoMecanicoDental').checked= false;

    // Selecciona la opcion solicitada
    switch (opcion) {
        case 1:
            document.getElementById('inptSeleccFiltroFechaTodosTrabajoMecanicoDental').checked= true;
            break;
        case 2:
            document.getElementById('inptSeleccFiltroFechaEntregaTrabajoMecanicoDental').checked= true;
            break;
        case 3:
            document.getElementById('inptSeleccFiltroFechaRetiroTrabajoMecanicoDental').checked= true;
            break;
    }
}

function buscarTrabajoMecanicosDentales() {
    let inptTipoTrabajoListadoTrabajoMecanicoDental= document.getElementById('inptTipoTrabajoListadoTrabajoMecanicoDental').value;
    const nombre_paciente= document.getElementById('inptPacienteListadoTrabajoMecanicoDental').value;
    const nombre_mecanico= document.getElementById('inptMecanicoListadoTrabajoMecanicoDental').value;
    const cod_trabajo= document.getElementById('inptCodListadoTrabajoMecanicoDental').value;
    const estado= document.getElementById('inptEstadoListadoTrabajoMecanicoDental').value;

    obtener_datos_user();
    let datos = new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "buscar");
    datos.append("tipo_trabajo", inptTipoTrabajoListadoTrabajoMecanicoDental);
    datos.append("nombre_paciente", nombre_paciente);
    datos.append("nombre_mecanico", nombre_mecanico);
    datos.append("cod_trabajo_mecanico_dental", cod_trabajo);
    datos.append("estado", estado);

    // Recopilar datos de los filtros
    if (filtro_fecha_trabajo_mecanico_dental == 2) {
        datos.append("fecha_entrega_desde", $("#filtro_fecha_desde").val());
        datos.append("fecha_entrega_hasta", $("#filtro_fecha_hasta").val());    
        datos.append("filtro_fecha", 'fecha_entrega');
    } else if(filtro_fecha_trabajo_mecanico_dental == 3) {
        datos.append("fecha_retiro_desde", $("#filtro_fecha_desde").val());
        datos.append("fecha_retiro_hasta", $("#filtro_fecha_hasta").val());
        datos.append('filtro_fecha', 'fecha_retiro');
    }
    
    verCerrarEfectoCargando("1");
    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmTrabajoMecanicoDental.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        error: function (jqXHR, textstatus, errorThrowm) {
            verCerrarEfectoCargando("");
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
            ver_vetana_informativa("Error de conexión al buscar registros.");
            document.getElementById('tablaTrabajoMecanicoDental').innerHTML= "";
        },
        success: function (responseText) {
            verCerrarEfectoCargando("");
            console.log(responseText);
            document.getElementById('tablaTrabajoMecanicoDental').innerHTML= "";
            try {
                var datos = $.parseJSON(responseText);
                if (datos["1"] === "exito") {
                    document.getElementById('tablaTrabajoMecanicoDental').innerHTML= datos["2"];
                    document.getElementById('inptRegistroNroTiposTrabajo').value= datos["3"];
                }
            } catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR");
                var titulo = "Error: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
            }
        }
    });
}

function limpiarFormularioTrabajoMecanicoDental() {
    cod_trabajo_mecanico_dental = "";
    idFkVenta= "";
    document.getElementById('inptTipoTrabajoMecanicoDental').value = "";
    document.getElementById('inptColorimetriaTrabajoMecanicoDental').value = "";
    document.getElementById('inptObservacionTrabajoMecanicoDental').innerHTML = "";
    document.getElementById('inptFechaEntregaTrabajoMecanicoDental').value = "";
    document.getElementById('inptFechaRetiroTrabajoMecanicoDental').value = "";
    document.getElementById('inptCostoTrabajoMecanicoDental').value = "";
    document.getElementById('inptPacienteTrabajoMecanicoDental').value= "";
    document.getElementById('inptPacienteCITrabajoMecanicoDental').value= "";
    document.getElementById('inptObservacionTrabajoMecanicoDental').value= "";
    document.getElementById('inptEstadoTrabajoMecanicoDental').value= "pendiente";
    document.getElementById('inptMecanicoTrabajoMecanicoDental').value= "";
    document.getElementById('inptDoctorTrabajoMecanicoDental').value= "";
    document.getElementById('btnEditarTrabajoMecanicoDental').style.backgroundColor= "#b7b7b7";
    document.getElementById('btnEditarTrabajoMecanicoDental').disabled= true;
    document.getElementById('btnAuditoriaTrabajoMecanicoMental').style.backgroundColor= "#b7b7b7";
    document.getElementById('btnAuditoriaTrabajoMecanicoMental').disabled= true;
}

function ObtenerdatosTrabajoMecanicoDental(elemento) {
    cod_trabajo_mecanico_dental = $(elemento).children('td[id="td_id"]').html();
    idFkVenta= $(elemento).children('td[id="td_datos_9"]').html();
    cod_tipo_trabajo_selected= $(elemento).children('td[id="td_datos_10"]').html();
    cod_mecanico_dental= $(elemento).children('td[id="td_datos_10"]').html();
    document.getElementById('inptTipoTrabajoMecanicoDental').value = $(elemento).children('td[id="td_datos_3"]').html();
    document.getElementById('inptColorimetriaTrabajoMecanicoDental').value = $(elemento).children('td[id="td_datos_6"]').html();
    document.getElementById('inptObservacionTrabajoMecanicoDental').innerHTML = $(elemento).children('td[id="td_datos_8"]').html();
    document.getElementById('inptFechaEntregaTrabajoMecanicoDental').value = $(elemento).children('td[id="td_datos_4"]').html();
    document.getElementById('inptFechaRetiroTrabajoMecanicoDental').value = $(elemento).children('td[id="td_datos_5"]').html();
    document.getElementById('inptCostoTrabajoMecanicoDental').value = $(elemento).children('td[id="td_datos_7"]').html();
    document.getElementById('inptPacienteTrabajoMecanicoDental').value= $(elemento).children('td[id="td_datos_1"]').html();
    document.getElementById('inptPacienteCITrabajoMecanicoDental').value= $(elemento).children('td[id="td_datos_17"]').html();
    document.getElementById('inptEstadoTrabajoMecanicoDental').value= $(elemento).children('td[id="td_datos_11"]').html().toLowerCase();
    document.getElementById('inptMecanicoTrabajoMecanicoDental').value= $(elemento).children('td[id="td_datos_10"]').html();
    document.getElementById('inptRegistroSeleccTrabajoMecanicoDental').value= $(elemento).children('td[id="td_id"]').html();
    document.getElementById('inptDoctorTrabajoMecanicoDental').value= $(elemento).children('td[id="td_datos_18"]').html();

    // Datos de auditoria
    document.getElementById('inptUsuarioInsertadoPor').value=$(elemento).children('td[id="td_datos_14"]').html()
	document.getElementById('inptFechaInsertadoPor').value=$(elemento).children('td[id="td_datos_13"]').html()
	document.getElementById('inptUsuarioEditadoPor').value=$(elemento).children('td[id="td_datos_16"]').html()
	document.getElementById('inptFechaEditadoPor').value=$(elemento).children('td[id="td_datos_15"]').html()

    $("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	elemento.className = 'tableRegistroSelec'
    document.getElementById('btnEditarTrabajoMecanicoDental').style.backgroundColor= "rgb(33, 150, 243)";
    document.getElementById('btnEditarTrabajoMecanicoDental').disabled= false;
    document.getElementById("btnAuditoriaTrabajoMecanicoMental").style.backgroundColor="#673ab7";
    document.getElementById('btnAuditoriaTrabajoMecanicoMental').disabled= false;
}

var cod_tipo_trabajo_selected = "";

function abmTipoTrabajo(descripcion, estado) {
    obtener_datos_user();
    var datos = new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("descripcion", descripcion);
    datos.append("estado", estado);

    if (cod_tipo_trabajo_selected === "") {
        datos.append("accion", "nuevo_tipo_trabajo");
    } else {
        datos.append("accion", "editar_tipo_trabajo");
        datos.append("cod_tipo_trabajo", cod_tipo_trabajo_selected);
    }

    verCerrarEfectoCargando("1");
    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmTrabajoMecanicoDental.php", // Se asume que abmTrabajoMecanicoDental.php maneja las acciones de tipo_trabajo
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        xhr: function () {
            var xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener("progress", function (evt) {
                var kb = ((evt.loaded * 1) / 1000).toFixed(1);
                if (kb == "0.0") { kb = 0.1; }
            }, false);
            xhr.addEventListener("progress", function (evt) {
                var kb = ((evt.loaded * 1) / 1000).toFixed(1);
                if (kb == "0.0") { kb = 0.1; }
            }, false);
            return xhr;
        },
        error: function (jqXHR, textstatus, errorThrowm) {
            verCerrarEfectoCargando("");
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
            ver_vetana_informativa("Error de conexión al procesar la solicitud de tipo de trabajo.");
        },
        success: function (responseText) {
            Respuesta = responseText;
            console.log(Respuesta);
            try {
                var datos = $.parseJSON(Respuesta);
                if (datos["1"] === "exito") {
                    ver_vetana_informativa("Operación realizada con éxito.");
                    buscarTiposTrabajo();
                    limpiarFormularioTipoTrabajo()
                } else if (datos["1"] === "UI") {
                    ver_vetana_informativa("Usuario o contraseña incorrectos.");
                } else {
                    ver_vetana_informativa("Ha ocurrido un error: " + (datos["mensaje"] || ""));
                }
            } catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR");
                var titulo = "Error: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
            } finally {
                verCerrarEfectoCargando("");
            }
        }
    });
}

function buscarTiposTrabajo() {
    obtener_datos_user();
    let datos = new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "buscar_tipo_trabajo");

    document.getElementById('ListTipoTrabajoMecanicoDental').innerHTML = "";
    document.getElementById('divBuscadorTipoTrabajoMecanicoDental').innerHTML= "";

    verCerrarEfectoCargando("1");
    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmTrabajoMecanicoDental.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        error: function (jqXHR, textstatus, errorThrowm) {
            verCerrarEfectoCargando("");
            manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
            ver_vetana_informativa("Error de conexión al buscar tipos de trabajo.");
        },
        success: function (responseText) {
            verCerrarEfectoCargando("");
            console.log(responseText);
            try {
                var datos = $.parseJSON(responseText);
                if (datos["1"] === "exito") {
                    document.getElementById('divBuscadorTipoTrabajoMecanicoDental').innerHTML= datos["2"];
                    document.getElementById('ListTipoTrabajoMecanicoDental').innerHTML = datos["4"];
                }
            } catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR");
                var titulo = "Error: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
            }
        }
    });
}

function verificaCamposTipoTrabajoParaEditar() {
    const descripcion= document.getElementById('inptNuevoTipoTrabajoMecanicoDental').value;
    const estado= document.getElementById('inptEstadoTipoTrabajoMecanicoDental').value;
    
    if (descripcion == "") {
        ver_vetana_informativa("El campo de descripcion no puede estar vacio.");
        return false;
    }

    abmTipoTrabajo(descripcion, estado)
}

function verCerrarVentanaTipoTrabajo(mostrar) {
	if(controlacceso("VERLISTADOTIPOTRABAJOMECANICODENTAL","accion")==false){ return;}
    if (mostrar) {
        $("div[id=divAbmTipoTrabajo]").fadeIn(250);
    } else {
        $("div[id=divAbmTipoTrabajo]").fadeOut(250);
    }
}

function limpiarFormularioTipoTrabajo() {
    cod_tipo_trabajo_selected = "";
    idFkVenta= "";
    $("#inptNuevoTipoTrabajoMecanicoDental").val("");
    $("#inptEstadoTipoTrabajoMecanicoDental").val("");
    document.getElementById('btnTipoTrabajoMecanicoDental').value= 'Guardar Datos';
}

function ObtenerdatosTipoTrabajo(elemento) {
    cod_tipo_trabajo_selected= $(elemento).children('td[id="td_id"]').html();
    document.getElementById('inptNuevoTipoTrabajoMecanicoDental').value= $(elemento).children('td[id="td_datos_1"]').html();
    document.getElementById('inptEstadoTipoTrabajoMecanicoDental').value = $(elemento).children('td[id="td_datos_2"]').html();
    document.getElementById('btnTipoTrabajoMecanicoDental').value= 'Editar Datos';
}

function minimizarVentanaTrabajoMecanicoDental() {
    document.getElementById('divAbmTrabajoMecanicoDental').style.display= 'none';
    document.getElementById('divMinimizadoTrabajoMecanicoDental').style.display= '';
}

function imprimirTicketTrabajoMecanDental() {
    const fecha_actual= new Date;
    const selectMecanicosDentales= document.getElementById('inptMecanicoTrabajoMecanicoDental');
    const selectDoctor= document.getElementById('inptDoctorTrabajoMecanicoDental')

    const tipo_trabajo= document.getElementById('inptTipoTrabajoMecanicoDental').value;
    const fecha_retiro= document.getElementById('inptFechaRetiroTrabajoMecanicoDental').value;
    const fecha_entrega= document.getElementById('inptFechaEntregaTrabajoMecanicoDental').value;
    const nombre_cliente= document.getElementById('inptPacienteTrabajoMecanicoDental').value;
    const colorimetria= document.getElementById('inptColorimetriaTrabajoMecanicoDental').value;
    const costo= document.getElementById('inptCostoTrabajoMecanicoDental').value;
    const observaciones= document.getElementById('inptObservacionTrabajoMecanicoDental').value;
    const ci_cliente = document.getElementById('inptPacienteCITrabajoMecanicoDental').value;
    const responsable= document.getElementById('inptUsuarioInsertadoPor').value;

    pagina = "<br><div style='background-color:#fff;'>"
        + "<center>"
        + "<div class='divTicket' style='width: 90%;border: solid 1px;border-radius: 10px; min-height: 450px;' > "
        + "<table style='width:100%; margin-left: 30px;'><tr>"
        + "<td style='width:150px'><b>CODIGO :</b></td>"
        + "<td style='width: 40%;'>" + cod_trabajo_mecanico_dental + "</td>"
        + "<td style='text-align: center;'>"
        + "<center><img src='/GoodVentaAsisCap/iconos/iconoEmpresa.JPG' style='height: 100px;' /></center>"
        + "</td>"
        + "</tr></table>"
        + "<div class='divSeparadorTicket' style='margin-bottom:5px'></div>"
        + "<table class='tableTicket' style='border: 1px solid black;border-collapse: collapse;'>"
        + "<tr>"
        + "<td style='width: 65%;border: 1px solid black;border-collapse: collapse;'><b>ENTREGA MECANICO DENTAL :</b></td>"
        + "<td style='width: 45%;border: 1px solid black;border-collapse: collapse;'><b>COSTO :</b>" + costo + "</td>"
        + "</tr>"
        + "<tr>"
        + "<td style='width: 65%;border: 1px solid black;border-collapse: collapse;'><b>PACIENTE :</b>" + nombre_cliente + "</td>"
        + "<td style='width: 45%;border: 1px solid black;border-collapse: collapse;'><b>FECHA RETIRO :</b>" + fecha_retiro + "</td>"
        + "</tr>"
        + "<tr>"
        + "<td style='width: 65%;border: 1px solid black;border-collapse: collapse;'><b>PACIENTE :</b>" + ci_cliente + "</td>"
        + "<td style='width: 45%;border: 1px solid black;border-collapse: collapse;'><b>FECHA RETIRO :</b>" + fecha_entrega + "</td>"
        + "</tr>"
        + "<tr>"
        + "<td style='width: 65%;border: 1px solid black;border-collapse: collapse;'><b>TIPO DE TRABAJO :</b>" + tipo_trabajo + "</td>"
        + "<td style='width: 45%;border: 1px solid black;border-collapse: collapse;'><b>COLORIMETRO :</b>" + colorimetria + "</td>"
        + "</tr>"
        + "</table>"
        + "<table class='tableTicket' style='border: 1px solid black;min-height: 250px;border-collapse: collapse;'> <tr>"
        + "<td style='display: flex;border: 1px solid black;border-collapse: collapse;'><b>OBSERVACIONES :</b>" + observaciones + "</td>"
        + "</tr> </table>"
        + "<table class='tableTicket' style='border: 1px solid black;border-collapse: collapse;min-height: 100px;'> <tr>"
        + "<td style='width:33%;text-align: center;vertical-align: bottom;padding-bottom: 8px;border: 1px solid black;border-collapse: collapse;'>"
        + "<b>FIRMA MECANICO</b>"
        + "<br>"+ selectMecanicosDentales.options[cod_mecanico_dental].text +"</b>"
        + "</td>"
        + "<td style='width:33%;text-align: center;vertical-align: bottom;padding-bottom: 8px;border: 1px solid black;border-collapse: collapse;'>"
        + "<b>FIRMA ENCARGADO</b>"
        + "<br>"+ responsable +"</b>"
        + "</td>"
        + "<td style='width:33%;text-align: center;vertical-align: bottom;padding-bottom: 8px;border: 1px solid black;border-collapse: collapse;'>"
        + "<b>FIRMA DOCTORA</b>"
        + "<br>"+ selectDoctor.options[selectDoctor.selectedIndex].text +"</b>"
        + "</td>"
        + "</tr> </table>"
        + "</td>"
        + "</tr></table>"
        + "<br>"
        + "</div>"
        + "</center>"
        + "</div>"

    // var ficha="<!DOCTYPE html><html><head></head><body>"+pagina+pagina+"</body></html>";
    var ficha = pagina;// + "<br><br><br>" + pagina;
    document.getElementById("DivImprimir").innerHTML = ficha;
    var documento = document.getElementById("DivImprimir").innerHTML;
    localStorage.setItem("reporte", documento);
    localStorage.setItem("tipo", "ticket");
    window.open("/GoodVentaAsisCap/system/reportTicket.html");
    document.getElementById("DivImprimir").innerHTML = "";

}
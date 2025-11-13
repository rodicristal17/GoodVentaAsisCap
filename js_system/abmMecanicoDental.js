var cod_mecanico_dental = "";
var filtro_fecha_mecanico_dental= 1;

function verCerrarVentanaMecanicoDental(mostrar, mostrarAbm) {
    if(controlacceso("VERLISTADOMECANICODENTAL","accion")==false){ return;}
    if (mostrar) {
        $("div[id=divAbmMecanicoDental]").fadeIn(250);
        if (mostrarAbm) {
            buscarTiposTrabajo();
            $("div[id=divAbmMecanicoDental1]").fadeIn(250);
            // Oculta el listado
            $("div[id=divAbmMecanicoDental2]").fadeOut(250);
        } else {
            buscarMecanicosDentales();
            $("div[id=divAbmMecanicoDental2]").fadeIn(250);
        }
    } else {
        if (mostrarAbm) {
            $("div[id=divAbmMecanicoDental1]").fadeOut(250);
            // Muestra el listado de vuelta
            $("div[id=divAbmMecanicoDental2]").fadeIn(250);
        } else {
            $("div[id=divAbmMecanicoDental2]").fadeOut(250);
            $("div[id=divAbmMecanicoDental]").fadeOut(250);
        }
    }
}

function verificarCamposMecanicoDental() {
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

    const observacion= $("#inptObservacionMecanicoDental").val()
    const colorimetro= $("#inptColorimetriaMecanicoDental").val()
    const costo= $("#inptCostoMecanicoDental").val()
    const fecha_entrega= $("#inptFechaEntregaMecanicoDental").val()
    const fecha_retiro= $("#inptFechaRetiroMecanicoDental").val()

    if (!inptTipoTrabajoMecanicoDental || inptTipoTrabajoMecanicoDental == "") {
        ver_vetana_informativa("Falta seleccionar el tipo de trabajo");
        return false;
    }
    if (fecha_entrega == "") {
        ver_vetana_informativa("Falta seleccionar la fecha de entrega");
        return false;
    }
    
    abmMecanicoDental(inptTipoTrabajoMecanicoDental,observacion,colorimetro,costo,fecha_entrega,fecha_retiro)
}

function abmMecanicoDental(cod_tipo_trabajoFK,observacion,colorimetro,costo,fecha_entrega,fecha_retiro) {
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
    datos.append("costo", costo);
    datos.append("fecha_entrega", fecha_entrega);
    datos.append("fecha_retiro", fecha_retiro);
    datos.append("cod_ventaFK", idFkVenta);
    if (cod_mecanico_dental == "") {
        datos.append("accion", "nuevo");
    } else {
        datos.append("accion", "editar");
        datos.append('cod_mecanico_dental', cod_mecanico_dental);
    }

    verCerrarEfectoCargando("1");
    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmMecanicoDental.php",
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
                    ver_vetana_informativa("Operación realizada con éxito.");
                    buscarMecanicosDentales(); // Recargar la lista
                    limpiarFormularioMecanicoDental();
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

function checkFechaMecanicoDental(opcion) {
    filtro_fecha_mecanico_dental= opcion;

    // Deselecciona las opciones
    document.getElementById('inptSeleccFiltroFechaTodosMecanicoDental').checked= false;
    document.getElementById('inptSeleccFiltroFechaEntregaMecanicoDental').checked= false;
    document.getElementById('inptSeleccFiltroFechaRetiroMecanicoDental').checked= false;

    // Selecciona la opcion solicitada
    switch (opcion) {
        case 1:
            document.getElementById('inptSeleccFiltroFechaTodosMecanicoDental').checked= true;
            break;
        case 2:
            document.getElementById('inptSeleccFiltroFechaEntregaMecanicoDental').checked= true;
            break;
        case 3:
            document.getElementById('inptSeleccFiltroFechaRetiroMecanicoDental').checked= true;
            break;
    }
}

function buscarMecanicosDentales() {
    let inptTipoTrabajoListadoMecanicoDental= document.getElementById('inptTipoTrabajoListadoMecanicoDental').value;

    obtener_datos_user();
    let datos = new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", "buscar");

    // Recopilar datos de los filtros
    if (filtro_fecha_mecanico_dental == 2) {
        datos.append("fecha_entrega_desde", $("#filtro_fecha_desde").val());
        datos.append("fecha_entrega_hasta", $("#filtro_fecha_hasta").val());    
        datos.append("filtro_fecha", 'fecha_entrega');
    } else if(filtro_fecha_mecanico_dental == 3) {
        datos.append("fecha_retiro_desde", $("#filtro_fecha_desde").val());
        datos.append("fecha_retiro_hasta", $("#filtro_fecha_hasta").val());
        datos.append('filtro_fecha', 'fecha_retiro');
    }
    datos.append("tipo_trabajo", inptTipoTrabajoListadoMecanicoDental);
    datos.append("nombre_paciente", $("#inptPacienteListadoMecanicoDental").val());
    
    verCerrarEfectoCargando("1");
    $.ajax({
        data: datos,
        url: "/GoodVentaAsisCap/php_system/abmMecanicoDental.php",
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
                    
                }
            } catch (error) {
                ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR");
                var titulo = "Error: " + error + " \r\n Consola: " + responseText;
                GuardarArchivosLog(titulo);
            }
        }
    });
}

function limpiarFormularioMecanicoDental() {
    cod_mecanico_dental = "";
    idFkVenta= "";
    document.getElementById('inptTipoTrabajoMecanicoDental').value = "";
    document.getElementById('inptColorimetriaMecanicoDental').value = "";
    document.getElementById('inptObservacionMecanicoDental').innerHTML = "";
    document.getElementById('inptFechaEntregaMecanicoDental').value = "";
    document.getElementById('inptFechaRetiroMecanicoDental').value = "";
    document.getElementById('inptCostoMecanicoDental').value = "";
    document.getElementById('inptPacienteMecanicoDental').value= "";
    document.getElementById('inptObservacionMecanicoDental').value= "";
    document.getElementById('btnEditarMecanicoDental').style.backgroundColor= "#b7b7b7";
    document.getElementById('btnEditarMecanicoDental').disabled= true;
}

function ObtenerdatosMecanicoDental(elemento) {
    cod_mecanico_dental = $(elemento).children('td[id="td_id"]').html();
    idFkVenta= $(elemento).children('td[id="td_datos_9"]').html();
    cod_tipo_trabajo_selected= $(elemento).children('td[id="td_datos_10"]').html();
    document.getElementById('inptTipoTrabajoMecanicoDental').value = $(elemento).children('td[id="td_datos_3"]').html();
    document.getElementById('inptColorimetriaMecanicoDental').value = $(elemento).children('td[id="td_datos_6"]').html();
    document.getElementById('inptObservacionMecanicoDental').innerHTML = $(elemento).children('td[id="td_datos_8"]').html();
    document.getElementById('inptFechaEntregaMecanicoDental').value = $(elemento).children('td[id="td_datos_4"]').html();
    document.getElementById('inptFechaRetiroMecanicoDental').value = $(elemento).children('td[id="td_datos_5"]').html();
    document.getElementById('inptCostoMecanicoDental').value = $(elemento).children('td[id="td_datos_7"]').html();
    document.getElementById('inptPacienteMecanicoDental').value= $(elemento).children('td[id="td_datos_1"]').html();

    $("tr[id=tbSelecRegistro]").each(function (i, td) {
		td.className = ''
	});
	elemento.className = 'tableRegistroSelec'
    document.getElementById('btnEditarMecanicoDental').style.backgroundColor= "#416c8f";
    document.getElementById('btnEditarMecanicoDental').disabled= false;
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
        url: "/GoodVentaAsisCap/php_system/abmMecanicoDental.php", // Se asume que abmMecanicoDental.php maneja las acciones de tipo_trabajo
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
        url: "/GoodVentaAsisCap/php_system/abmMecanicoDental.php",
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
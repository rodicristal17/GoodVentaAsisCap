function abrirInformeLogInactivos() {
	var ventana = document.getElementById("divInformeLogInactivos");
	if (!ventana) { return; }

	ventana.style.display = "block";
	var minimizado = document.getElementById("divMinimizadoInformeLogInactivos");
	if (minimizado) {
		minimizado.style.display = "none";
	}
	buscarInformeLogInactivos();
}

function cerrarInformeLogInactivos() {
	var ventana = document.getElementById("divInformeLogInactivos");
	if (!ventana) { return; }

	ventana.style.display = "none";
}

function verCerrarInformeLogInactivos(mostrar) {
	if (mostrar) {
		abrirInformeLogInactivos();
		return;
	}

	cerrarInformeLogInactivos();
}

function minimizarInformeLogInactivos() {
	document.getElementById("divMinimizadoInformeLogInactivos").style.display = "";
	document.getElementById("divInformeLogInactivos").style.display = "none";
}

function buscarInformeLogInactivos() {
	obtener_datos_user();

	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("accion", "buscar");
	datos.append("tabla", document.getElementById("inptLogInactivosTabla").value);
	datos.append("registro", document.getElementById("inptLogInactivosRegistro").value);
	datos.append("usuario", document.getElementById("inptLogInactivosUsuario").value);
	datos.append("fecha_desde", document.getElementById("inptLogInactivosFechaDesde").value);
	datos.append("fecha_hasta", document.getElementById("inptLogInactivosFechaHasta").value);

	verCerrarEfectoCargando("1");
	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmLogInactivos.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		error: function (jqXHR, textstatus) {
			verCerrarEfectoCargando("");
			manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
			ver_vetana_informativa("SE HA PRODUCTIDO UN ERROR");
		},
		success: function (responseText) {
			try {
				var respuesta = $.parseJSON(responseText);
				if (respuesta["1"] == "exito") {
					document.getElementById("table_InformeLogInactivos").innerHTML = respuesta["2"];
					document.getElementById("inptTotalRegistroLogInactivos").value = respuesta["4"];
				} else {
					ver_vetana_informativa(respuesta["2"] || "No se pudo consultar el log.");
				}
			} catch (error) {
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR");
				GuardarArchivosLog("Error: " + error + " \r\n Consola: " + responseText);
			} finally {
				verCerrarEfectoCargando("");
			}
		}
	});
}
